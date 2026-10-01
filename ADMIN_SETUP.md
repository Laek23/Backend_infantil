# Initial admin account on Render

The first administrator is created by `php artisan db:seed --force` when the backend container starts. To bootstrap it without committing credentials:

1. In the Render backend service, add `ADMIN_NAME`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD` under Environment.
2. Use a new email address and a unique password with at least 12 characters. Never put these values in Git or share the password in chat.
3. Deploy the backend and wait for the deploy to finish. The seeder creates the admin only if that email does not already exist.
4. Sign in to the Android app with that email and password. The account opens the admin center because its role is `admin`.
5. Remove the three bootstrap variables from Render after confirming login. The existing admin account remains in the database.

If `ADMIN_EMAIL` already belongs to a child account, deployment stops rather than promoting that account. Choose a different administrator email.
