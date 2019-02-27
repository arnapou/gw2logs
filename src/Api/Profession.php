<?php

/*
 * This file is part of the Arnapou gw2logs package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api;

use App\Utils;

class Profession
{
    /**
     * @param $player
     * @return array
     */
    public static function fromPlayer($player)
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
    private static function getAllProfessions()
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
