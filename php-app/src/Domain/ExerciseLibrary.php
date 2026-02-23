<?php

declare(strict_types=1);

namespace App\Domain;

final class ExerciseLibrary
{
    /** @return array<int, array<string,mixed>> */
    public static function all(): array
    {
        return [
            [
                'slug' => 'sit-to-stand',
                'titel' => 'Sit-to-stand (opstaan uit stoel)',
                'categorie' => 'kracht',
                'doel' => 'Vergroot kracht in benen en vertrouwen bij opstaan.',
                'veiligheid' => 'Gebruik een stevige stoel en zet beide voeten plat op de vloer.',
                'stappen' => ['Ga rechtop op een stoel zitten.', 'Plaats voeten op heupbreedte.', 'Sta rustig op met steun indien nodig.', 'Ga gecontroleerd weer zitten.'],
                'niveau' => 'makkelijk',
                'reps_of_time' => '2 x 6 herhalingen',
                'video_url' => '',
            ],
            [
                'slug' => 'hielheffen-met-steun',
                'titel' => 'Hielheffen met steun',
                'categorie' => 'balans',
                'doel' => 'Verbetert stabiliteit van enkels en onderbenen.',
                'veiligheid' => 'Houd steun vast aan aanrecht of stoel.',
                'stappen' => ['Sta rechtop met lichte steun.', 'Kom rustig op de tenen.', 'Houd 1 seconde vast.', 'Zak rustig terug.'],
                'niveau' => 'makkelijk',
                'reps_of_time' => '2 x 8 herhalingen',
                'video_url' => '',
            ],
            [
                'slug' => 'teenheffen-met-steun',
                'titel' => 'Teenheffen met steun',
                'categorie' => 'balans',
                'doel' => 'Helpt bij stabiel landen van de voet tijdens lopen.',
                'veiligheid' => 'Blijf dicht bij steun en beweeg rustig.',
                'stappen' => ['Sta rechtop met steun.', 'Til de tenen op terwijl hielen blijven staan.', 'Houd kort vast.', 'Laat tenen rustig zakken.'],
                'niveau' => 'makkelijk',
                'reps_of_time' => '2 x 8 herhalingen',
                'video_url' => '',
            ],
            [
                'slug' => 'zijstappen-langs-aanrecht',
                'titel' => 'Zijstappen langs aanrecht',
                'categorie' => 'balans',
                'doel' => 'Verbetert zijwaartse balans en controle.',
                'veiligheid' => 'Gebruik aanrecht als steun en kijk vooruit.',
                'stappen' => ['Sta zijwaarts langs het aanrecht.', 'Zet een kleine stap opzij.', 'Sluit de andere voet aan.', 'Herhaal rustig heen en terug.'],
                'niveau' => 'standaard',
                'reps_of_time' => '2 x 1 minuut',
                'video_url' => '',
            ],
            [
                'slug' => 'marcheren-op-de-plaats',
                'titel' => 'Marcheren op de plaats met steun',
                'categorie' => 'kracht',
                'doel' => 'Helpt bij ritme, heupkracht en zekerder lopen.',
                'veiligheid' => 'Houd steun vast en blijf in een rustig tempo.',
                'stappen' => ['Sta rechtop met steun.', 'Til afwisselend een knie rustig op.', 'Adem door en blijf rechtop.', 'Verlaag tempo bij vermoeidheid.'],
                'niveau' => 'makkelijk',
                'reps_of_time' => '2 x 45 seconden',
                'video_url' => '',
            ],
            [
                'slug' => 'tandem-stand-bij-steun',
                'titel' => 'Tandem stand bij steun',
                'categorie' => 'zekerheid',
                'doel' => 'Trainen van balans in smalle stand.',
                'veiligheid' => 'Doe dit naast aanrecht of stevige tafel.',
                'stappen' => ['Zet één voet recht voor de andere.', 'Houd lichte steun vast.', 'Blijf rustig staan en adem door.', 'Wissel daarna van voet voor.'],
                'niveau' => 'standaard',
                'reps_of_time' => '2 x 20 seconden per zijde',
                'video_url' => '',
            ],
            [
                'slug' => 'enkelcirkels',
                'titel' => 'Enkelcirkels (mobiliteit)',
                'categorie' => 'mobiliteit',
                'doel' => 'Houdt enkelgewrichten soepel voor stabieler lopen.',
                'veiligheid' => 'Doe dit zittend op een stabiele stoel.',
                'stappen' => ['Ga rechtop zitten.', 'Til één voet iets op.', 'Draai rustig kleine cirkels met de enkel.', 'Wissel naar de andere voet.'],
                'niveau' => 'makkelijk',
                'reps_of_time' => '2 x 10 cirkels per richting',
                'video_url' => '',
            ],
            [
                'slug' => 'gewicht-verplaatsen',
                'titel' => 'Gewicht verplaatsen links/rechts met steun',
                'categorie' => 'balans',
                'doel' => 'Verbetert controle tijdens draaien en verplaatsen.',
                'veiligheid' => 'Houd licht steun vast en verplaats gewicht langzaam.',
                'stappen' => ['Sta met voeten op heupbreedte.', 'Verplaats gewicht rustig naar links.', 'Ga rustig naar rechts.', 'Blijf met bovenlichaam rechtop.'],
                'niveau' => 'standaard',
                'reps_of_time' => '2 x 1 minuut',
                'video_url' => '',
            ],
        ];
    }

    /** @return array<string,mixed>|null */
    public static function findBySlug(string $slug): ?array
    {
        foreach (self::all() as $exercise) {
            if ($exercise['slug'] === $slug) {
                return $exercise;
            }
        }

        return null;
    }
}
