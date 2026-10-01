<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Stripe\Webhook;

class BillingController extends Controller
{
    public function checkout(Request $request): JsonResponse
    {
        abort_if($request->user()->role === 'admin', 403, 'Los administradores no necesitan vidas.');

        if (blank(config('services.stripe.secret')) || blank(config('services.frontend_url'))) {
            return response()->json(['message' => 'Falta configurar STRIPE_SECRET o FRONTEND_URL en Render.'], 503);
        }

        if ($caBundlePath = config('services.stripe.ca_bundle_path')) {
            \Stripe\Stripe::setCABundlePath($caBundlePath);
        }

        $stripe = new StripeClient(config('services.stripe.secret'));
        try {
            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'line_items' => [[
                    'price_data' => [
                        'currency' => config('services.stripe.currency', 'mxn'),
                        'product_data' => ['name' => 'Plan Aventum Kids'],
                        'unit_amount' => 12000,
                    ],
                    'quantity' => 1,
                ]],
                'customer_email' => $request->user()->email,
                'client_reference_id' => (string) $request->user()->id,
                'metadata' => ['user_id' => (string) $request->user()->id],
                'success_url' => rtrim(config('services.frontend_url'), '/') . '/?payment=success&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => rtrim(config('services.frontend_url'), '/') . '/?payment=cancelled',
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'Stripe no está disponible en este momento.'], 503);
        }

        return response()->json(['url' => $session->url]);
    }

    public function confirm(Request $request): JsonResponse
    {
        abort_if($request->user()->role === 'admin', 403, 'Los administradores no necesitan vidas.');
        $data = $request->validate(['session_id' => ['required', 'string']]);

        if ($caBundlePath = config('services.stripe.ca_bundle_path')) {
            \Stripe\Stripe::setCABundlePath($caBundlePath);
        }

        try {
            $session = (new StripeClient(config('services.stripe.secret')))->checkout->sessions->retrieve($data['session_id']);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'No se pudo confirmar el pago.'], 503);
        }

        if ($session->payment_status !== 'paid' || (string) ($session->metadata->user_id ?? '') !== (string) $request->user()->id) {
            return response()->json(['message' => 'El pago aún no está confirmado.'], 422);
        }

        $this->grantBenefits($request->user(), $session->id);

        return response()->json(['message' => 'Pago confirmado.', 'lives' => 14, 'premium_days' => 30]);
    }

    public function webhook(Request $request): JsonResponse
    {
        $signature = $request->header('Stripe-Signature');
        try {
            $event = Webhook::constructEvent($request->getContent(), $signature, env('STRIPE_WEBHOOK_SECRET'));
        } catch (\Throwable $exception) {
            return response()->json(['message' => 'Firma de Stripe no válida.'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            if (($session->payment_status ?? null) === 'paid') {
                $user = User::find($session->metadata->user_id ?? $session->client_reference_id);
                if ($user && $user->role !== 'admin') $this->grantBenefits($user, $session->id);
            }
        }

        return response()->json(['received' => true]);
    }

    private function grantBenefits(User $user, string $sessionId): void
    {
        if ($user->stripe_checkout_session_id === $sessionId) return;
        $base = $user->premium_until && $user->premium_until->isFuture() ? $user->premium_until : now();
        $user->forceFill([
            'lives' => 14,
            'lives_reset_at' => now(),
            'premium_until' => $base->addDays(30),
            'stripe_checkout_session_id' => $sessionId,
        ])->save();
    }
}