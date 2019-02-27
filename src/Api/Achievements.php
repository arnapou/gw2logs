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

class Achievements
{
    /**
     * @return array
     */
    private static function getCategoryIds()
    {
        return [
            'W1' => 124,
            'W2' => 134,
            'W3' => 138,
            'W4' => 155,
            'W5' => 195,
            'W6' => 215,
        ];
    }

    /**
     * @param $accessToken
     * @return mixed|null
     */
    public static function unlocked($accessToken)
    {
        return Utils::cached(
            'api_achiev_unlocked_' . md5($accessToken . serialize(self::getAchievementsIds())),
            function () use ($accessToken) {
                $ids = rawurlencode(implode(',', self::getAchievementsIds()));
                return self::idAsKey(
                    Utils::curl('GET', 'https://api.guildwars2.com/v2/account/achievements?access_token=' . $accessToken . '&ids=' . $ids)
                );
            },
            120
        );
    }

    /**
     * @return array
     */
    public static function getAchievementsIds()
    {
        $ids = [];
        foreach (self::getCategories() as $category) {
            foreach (($category['achievements'] ?? []) as $id) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    /**
     * @return array
     */
    public static function getAchievements($accessToken = null)
    {
        $achievs  = Utils::cached(
            'api_achiev_detail_' . md5(serialize(self::getAchievementsIds())),
            function () {
                $ids = rawurlencode(implode(',', self::getAchievementsIds()));
                return self::idAsKey(
                    Utils::curl('GET', 'https://api.guildwars2.com/v2/achievements?lang=fr&ids=' . $ids)
                );
            },
            3600 * 12
        );
        $unlocked = $accessToken ? self::unlocked($accessToken) : [];

        $details = [];
        foreach (self::getCategories() as $category) {
            $nbUnlocked = 0;
            $nbTotal    = 0;
            $detail     = [];
            foreach (($category['achievements'] ?? []) as $id) {
                if (isset($achievs[$id])) {
                    $detail[$id]                = $achievs[$id];
                    $detail[$id]['rewardTypes'] = self::rewardTypes($achievs[$id], $titleId);
                    $detail[$id]['unlocked']    = isset($unlocked[$id]) ? self::unlockedPct($unlocked[$id]) : false;
                    $detail[$id]['titleName']   = $titleId ? (Title::get($titleId)['name'] ?? '') : '';
                    $nbTotal++;
                    $nbUnlocked += $detail[$id]['unlocked'] ? 1 : 0;
                }
            }
            $category['achievements'] = $detail;
            $category['pct']          = $nbTotal > 0 ? round(100 * $nbUnlocked / $nbTotal, 1) : 0;
            $details[]                = $category;
        }
        return $details;
    }

    /**
     * @param $data
     * @return float|int
     */
    public static function unlockedPct($data)
    {
        if ($data['done'] ?? true) {
            return 1;
        } elseif (isset($data['current'], $data['max'])) {
            return $data['max'] ? round($data['current'] / $data['max'], 2) : 0;
        }
        return 0;
    }

    /**
     * @return array
     */
    public static function getCategories()
    {
        return Utils::cached(
            'api_achiev_categories_' . md5(serialize(self::getCategoryIds())),
            function () {
                $ids = rawurlencode(implode(',', self::getCategoryIds()));
                return self::idAsKey(
                    Utils::curl('GET', 'https://api.guildwars2.com/v2/achievements/categories?lang=en&ids=' . $ids)
                );
            },
            3600 * 12
        );
    }

    /**
     * @param $data
     * @param $titleId
     * @return array
     */
    private static function rewardTypes($data, &$titleId)
    {
        $titleId = null;
        $types   = [];
        foreach (($data['rewards'] ?? []) as $reward) {
            if ($reward['type'] ?? false) {
                $type = strtolower($reward['type']);
                if ($type === 'title' && isset($reward['id'])) {
                    $titleId = $reward['id'];
                }
                $types[] = $type;
            }
        }
        return $types;
    }

    /**
     * @param $items
     * @return array
     */
    private static function idAsKey($items)
    {
        $return = [];
        foreach ($items as $item) {
            if (isset($item['id'])) {
                $return[$item['id']] = $item;
            }
        }
        return $return;
    }
}
