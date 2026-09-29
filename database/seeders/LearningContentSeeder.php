<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Reward;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class LearningContentSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            'matematicas' => ['Matemáticas', 'El Planeta de los Números', 'rocket_launch', '#005ea4'],
            'espanol' => ['Español', 'El Bosque de las Letras', 'auto_stories', '#1e862d'],
            'historia' => ['Historia', 'La Máquina del Tiempo', 'schedule', '#785900'],
        ];

        foreach ($subjects as $slug => [$name, $description, $icon, $accent]) {
            $subject = Subject::updateOrCreate(['slug' => $slug], compact('name', 'description', 'icon', 'accent') + ['active' => true]);
            foreach ($this->activities()[$slug] as $position => $activity) {
                Activity::updateOrCreate(
                    ['subject_id' => $subject->id, 'title' => $activity['title']],
                    $activity + ['subject_id' => $subject->id, 'active' => true, 'position' => $position],
                );
            }
        }

        foreach ([
            ['Búho Sabio', 'sticker', 'psychology', 'Compañero de tus primeras aventuras', 10],
            ['Gafas Clásicas', 'glasses', 'glasses', 'Un accesorio de explorador', 30],
            ['Aura Estelar', 'aura', 'stars', 'Brillo de gran descubridor', 60],
            ['Uniforme Cadete', 'uniform', 'uniform', 'Ropa de la academia aventurera', 100],
            ['Rango Élite', 'rank', 'military_tech', 'Distinción por constancia', 150],
        ] as [$name, $category, $asset, $description, $unlockXp]) {
            Reward::updateOrCreate(['name' => $name], [
                'category' => $category,
                'asset' => $asset,
                'description' => $description,
                'unlock_xp' => $unlockXp,
                'active' => true,
            ]);
        }
    }

    private function activities(): array
    {
        return [
            'matematicas' => [
                ['type' => 'sumador', 'title' => 'Sumador de Cohetes', 'prompt' => 'Suma las piezas para lanzar el cohete.', 'hint' => '4 + 3 = ?', 'answer' => '7', 'content' => ['numbers' => [4, 3], 'options' => ['6', '7', '8', '9']]],
                ['type' => 'sumador', 'title' => 'Mercado Galáctico', 'prompt' => 'Junta las monedas y descubre el total.', 'hint' => '6 + 2 = ?', 'answer' => '8', 'content' => ['numbers' => [6, 2], 'options' => ['7', '8', '9', '10']]],
                ['type' => 'memorama', 'title' => 'Memorama de Planetas', 'prompt' => 'Encuentra las parejas de planetas iguales.', 'hint' => 'Voltea dos tarjetas por turno.', 'answer' => '', 'content' => ['cards' => ['🪐', '🚀', '🌙', '🪐', '🚀', '🌙']]],
                ['type' => 'memorama', 'title' => 'Parejas Numéricas', 'prompt' => 'Recuerda dónde están los números iguales.', 'hint' => 'Hay tres parejas escondidas.', 'answer' => '', 'content' => ['cards' => ['2', '5', '8', '2', '5', '8']]],
                ['type' => 'tiempo', 'title' => 'Cuenta Regresiva', 'prompt' => 'Resuelve la suma antes de que termine el tiempo.', 'hint' => '¡Tienes 20 segundos!', 'answer' => '12', 'content' => ['numbers' => [7, 5], 'options' => ['10', '11', '12', '13'], 'seconds' => 20]],
                ['type' => 'tiempo', 'title' => 'Carrera de Multiplicar', 'prompt' => 'Elige el resultado antes de que acabe el reloj.', 'hint' => '¡Tienes 15 segundos!', 'answer' => '18', 'content' => ['numbers' => [3, 6], 'operator' => 'x', 'options' => ['15', '16', '18', '21'], 'seconds' => 15]],
                ['type' => 'adivina', 'title' => 'Adivina el Número', 'prompt' => 'Soy mayor que 10 y menor que 12. ¿Quién soy?', 'hint' => 'Escríbelo con número.', 'answer' => '11', 'content' => ['placeholder' => 'Tu número'] ],
                ['type' => 'adivina', 'title' => 'El Número Misterioso', 'prompt' => 'Si tengo dos decenas y cuatro unidades, ¿qué número soy?', 'hint' => 'Decenas y unidades te dan la pista.', 'answer' => '24', 'content' => ['placeholder' => 'Tu número'] ],
                ['type' => 'crucigrama', 'title' => 'La Mitad de Diez', 'prompt' => 'Escribe el número que completa la pista.', 'hint' => '5 + 5 = ?', 'answer' => '10', 'content' => ['clue' => 'Número que resulta de sumar cinco y cinco.']],
                ['type' => 'crucigrama', 'title' => 'Formas con Lados', 'prompt' => 'Adivina la figura por sus lados.', 'hint' => 'Tiene tres lados.', 'answer' => 'TRIANGULO', 'content' => ['clue' => 'Figura geométrica que tiene tres lados.']],
            ],
            'espanol' => [
                ['type' => 'sopa', 'title' => 'Sopa de Letras del Bosque', 'prompt' => 'Encuentra la palabra LUNA letra por letra.', 'hint' => 'Palabra escondida: LUNA', 'answer' => 'LUNA', 'content' => ['word' => 'LUNA', 'grid' => ['L', 'A', 'R', 'M', 'U', 'N', 'S', 'T', 'N', 'O', 'P', 'A']]],
                ['type' => 'sopa', 'title' => 'Sopa de Animales', 'prompt' => 'Encuentra la palabra OSO en la sopa.', 'hint' => 'Palabra escondida: OSO', 'answer' => 'OSO', 'content' => ['word' => 'OSO', 'grid' => ['O', 'B', 'S', 'E', 'R', 'O', 'C', 'A', 'L', 'O', 'S', 'D']]],
                ['type' => 'memorama', 'title' => 'Memorama de Vocales', 'prompt' => 'Encuentra las vocales que forman pareja.', 'hint' => 'Voltea dos tarjetas por turno.', 'answer' => '', 'content' => ['cards' => ['A', 'E', 'I', 'A', 'E', 'I']]],
                ['type' => 'memorama', 'title' => 'Palabras Gemelas', 'prompt' => 'Busca las dos tarjetas con la misma palabra.', 'hint' => 'Cada palabra aparece dos veces.', 'answer' => '', 'content' => ['cards' => ['SOL', 'PAN', 'MAR', 'SOL', 'PAN', 'MAR']]],
                ['type' => 'tiempo', 'title' => 'Acento Relámpago', 'prompt' => 'Elige la palabra escrita correctamente antes de que acabe el reloj.', 'hint' => '¡Tienes 20 segundos!', 'answer' => 'ARBOL', 'content' => ['options' => ['ARBOL', 'ARVOL', 'ALBOL', 'ARBO'], 'seconds' => 20]],
                ['type' => 'tiempo', 'title' => 'Sílabas a Toda Prisa', 'prompt' => '¿Cuántas sílabas tiene mariposa?', 'hint' => '¡Tienes 15 segundos!', 'answer' => '4', 'content' => ['options' => ['2', '3', '4', '5'], 'seconds' => 15]],
                ['type' => 'adivina', 'title' => 'Adivina la Palabra', 'prompt' => 'Animal que dice miau y persigue ratones.', 'hint' => 'Empieza con G.', 'answer' => 'GATO', 'content' => ['placeholder' => 'Escribe el animal'] ],
                ['type' => 'adivina', 'title' => 'La Palabra Escondida', 'prompt' => 'Fruta amarilla que se pela y les encanta a los monos.', 'hint' => 'Empieza con P.', 'answer' => 'PLATANO', 'content' => ['placeholder' => 'Escribe la fruta'] ],
                ['type' => 'crucigrama', 'title' => 'La Casa de los Libros', 'prompt' => 'Completa la palabra según la pista.', 'hint' => 'Empieza con B.', 'answer' => 'BIBLIOTECA', 'content' => ['clue' => 'Lugar donde puedes leer y pedir libros prestados.']],
                ['type' => 'crucigrama', 'title' => 'Pequeño y Diminuto', 'prompt' => 'Escribe el antónimo de grande.', 'hint' => 'Tiene cinco letras.', 'answer' => 'CHICO', 'content' => ['clue' => 'Palabra que significa lo contrario de grande.']],
            ],
            'historia' => [
                ['type' => 'crucigrama', 'title' => 'Crucigrama del Tiempo', 'prompt' => 'Completa las casillas con la respuesta de la pista.', 'hint' => 'Pista 1 · 6 letras', 'answer' => 'ATENAS', 'content' => ['clue' => 'Ciudad donde nacieron los Juegos Olímpicos antiguos.']],
                ['type' => 'crucigrama', 'title' => 'Crucigrama de Civilizaciones', 'prompt' => 'Completa las casillas con la respuesta de la pista.', 'hint' => 'Pista 2 · 8 letras', 'answer' => 'PIRAMIDE', 'content' => ['clue' => 'Construcción egipcia con forma triangular.']],
                ['type' => 'memorama', 'title' => 'Memorama de Civilizaciones', 'prompt' => 'Encuentra parejas de objetos de la antigüedad.', 'hint' => 'Voltea dos tarjetas por turno.', 'answer' => '', 'content' => ['cards' => ['🏺', '🏛️', '📜', '🏺', '🏛️', '📜']]],
                ['type' => 'memorama', 'title' => 'Parejas del Pasado', 'prompt' => 'Recuerda dónde viste cada objeto histórico.', 'hint' => 'Encuentra las tres parejas.', 'answer' => '', 'content' => ['cards' => ['FARAON', 'TEMPLO', 'BARCO', 'FARAON', 'TEMPLO', 'BARCO']]],
                ['type' => 'tiempo', 'title' => 'Reloj de la Historia', 'prompt' => 'Elige el invento que sirve para medir el tiempo.', 'hint' => '¡Tienes 20 segundos!', 'answer' => 'RELOJ', 'content' => ['options' => ['RELOJ', 'CASCO', 'LANZA', 'JARRON'], 'seconds' => 20]],
                ['type' => 'tiempo', 'title' => 'Viaje Relámpago', 'prompt' => '¿En qué país se construyeron las pirámides de Guiza?', 'hint' => '¡Tienes 15 segundos!', 'answer' => 'EGIPTO', 'content' => ['options' => ['ROMA', 'EGIPTO', 'CHINA', 'GRECIA'], 'seconds' => 15]],
                ['type' => 'adivina', 'title' => 'Adivina el Personaje', 'prompt' => 'Fui una reina del antiguo Egipto. ¿Quién soy?', 'hint' => 'Mi nombre empieza con C.', 'answer' => 'CLEOPATRA', 'content' => ['placeholder' => 'Escribe el nombre'] ],
                ['type' => 'adivina', 'title' => 'El Viajero del Tiempo', 'prompt' => 'Soy una persona que estudia objetos y restos del pasado.', 'hint' => 'Empieza con A.', 'answer' => 'ARQUEOLOGO', 'content' => ['placeholder' => '¿Qué profesión soy?'] ],
                ['type' => 'sopa', 'title' => 'Sopa de la Antigua Roma', 'prompt' => 'Encuentra la palabra ROMA entre las letras.', 'hint' => 'Busca cuatro letras en orden.', 'answer' => 'ROMA', 'content' => ['word' => 'ROMA', 'grid' => ['R', 'A', 'M', 'O', 'S', 'T', 'O', 'M', 'A', 'P', 'L', 'E']]],
                ['type' => 'sopa', 'title' => 'Sopa de Inventos', 'prompt' => 'Encuentra la palabra RUEDA.', 'hint' => 'Busca cinco letras en orden.', 'answer' => 'RUEDA', 'content' => ['word' => 'RUEDA', 'grid' => ['R', 'U', 'E', 'D', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']]],
            ],
        ];
    }
}