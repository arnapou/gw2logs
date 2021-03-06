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

class Raids
{
    /**
     * @param $accessToken
     * @return array
     */
    public static function progress($accessToken)
    {
        $raids    = self::getAllRaids();
        $unlocked = self::unlocked($accessToken);
        $progress = [];
        $wingNum  = 1;
        $total    = 0;
        $num      = 0;

        foreach ($raids as $raid) {
            $table = [];
            $cols  = 0;
            foreach ($raid['wings'] as $wing) {
                $bossNum = 1;
                $cases   = [];
                foreach ($wing['events'] as $event) {
                    $done = \in_array($event['id'], $unlocked);
                    $txt  = '?';
                    if ($event['type'] == 'Boss') {
                        $txt = 'B' . ($bossNum++);
                        $total++;
                        $num += $done ? 1 : 0;
                    } elseif ($event['type'] == 'Checkpoint') {
                        $txt = 'E';
                    }

                    $cases[] = [$txt, $done];
                }
                $cols    = \count($cases) > $cols ? \count($cases) : $cols;
                $table[] = ['title' => 'W' . ($wingNum++), 'cases' => $cases];
            }
            $progress[] = [
                'title' => self::cleanText($raid['id']),
                'table' => $table,
                'cols'  => $cols,
            ];
        }
        $progress = [
            'num'   => $num,
            'total' => $total,
            'raids' => $progress,
        ];
        return $progress;
    }

    private static function cleanText($str)
    {
        return ucfirst(str_replace('_', ' ', $str));
    }

    /**
     * @param $accessToken
     * @return mixed|null
     */
    private static function unlocked($accessToken)
    {
        return Utils::cached(
            'api_raids_unlocked_' . md5($accessToken),
            function () use ($accessToken) {
                return Utils::curl('GET', 'https://api.guildwars2.com/v2/account/raids?access_token=' . $accessToken);
            },
            120
        );
    }

    /**
     * @return array
     */
    private static function getAllRaids()
    {
        return Utils::cached(
            'api_raids_all',
            function () {
                return Utils::curl('GET', 'https://api.guildwars2.com/v2/raids?ids=all');
            },
            3600
        );
    }
}
