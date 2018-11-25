<?php


namespace App\Api;


use App\Utils;

class Profession
{
    /**
     * @param $player
     * @return array
     */
    static public function fromPlayer($player)
    {
        $elite_spec = $player['elite_spec'] ?? 0;
        foreach (self::getAllProfessions() as $profession) {
            foreach (($profession['specializations'] ?? []) as $specialization) {
                if ($specialization == $elite_spec) {
                    return [
                        'profession_name' => $profession['id'] ?? null,
                        'profession_icon' => $profession['icon'] ?? null,
                    ];
                }
            }
        }
        return [
            'profession_name' => null,
            'profession_icon' => null,
        ];
    }

    /**
     * @return mixed|null
     */
    static private function getAllProfessions()
    {
        return Utils::cached(
            'api_professions_all',
            function () {
                return Utils::curl('GET', 'https://api.guildwars2.com/v2/professions?ids=all');
            },
            3600*12
        );
    }
}