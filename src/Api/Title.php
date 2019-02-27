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

class Title
{
    /**
     * @param $id
     * @return array
     */
    public static function get($id)
    {
        return Utils::cached(
            'api_title_fr_' . $id,
            function () use ($id) {
                return Utils::curl('GET', 'https://api.guildwars2.com/v2/titles/' . $id . '?lang=fr');
            },
            3600 * 24 * 7
        );
    }
}
