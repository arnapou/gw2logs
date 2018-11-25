<?php


namespace App\Api;


use App\Utils;

class Achievements
{

    /**
     * @return array
     */
    static private function getCategoryIds()
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
    static public function unlocked($accessToken)
    {
        return Utils::cached(
            'api_achiev_unlocked_' . md5($accessToken . serialize(self::getAchievementsIds())),
            function () use ($accessToken) {
                $ids = rawurlencode(implode(",", self::getAchievementsIds()));
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
    static public function getAchievementsIds()
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
    static public function getAchievements($accessToken = null)
    {
        $achievs  = Utils::cached(
            'api_achiev_detail_' . md5(serialize(self::getAchievementsIds())),
            function () {
                $ids = rawurlencode(implode(",", self::getAchievementsIds()));
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
                    $detail[$id]['unlocked']    = isset($unlocked[$id]) ? ($unlocked[$id]['done']): false;
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
     * @return array
     */
    static public function getCategories()
    {
        return Utils::cached(
            'api_achiev_categories_' . md5(serialize(self::getCategoryIds())),
            function () {
                $ids = rawurlencode(implode(",", self::getCategoryIds()));
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
    static private function rewardTypes($data, &$titleId)
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
    static private function idAsKey($items)
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