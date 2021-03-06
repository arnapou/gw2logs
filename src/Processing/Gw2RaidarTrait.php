<?php

/*
 * This file is part of the Arnapou gw2logs package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Processing;

use App\Utils;

trait Gw2RaidarTrait
{
    /**
     * @return array
     * @throws \Exception
     */
    private function getAuthorizationHeaders()
    {
        return ['Authorization: Token ' . $this->getToken()];
    }

    /**
     * @return string
     */
    private function getToken()
    {
        return Utils::cached(
            'gw2raidar_token_' . md5(GW2RAIDAR_USER),
            function () {
                $data = Utils::curl('POST', GW2RAIDAR_URL . 'api/v2/token', [
                    'username' => GW2RAIDAR_USER,
                    'password' => GW2RAIDAR_PASS,
                ]);
                if (empty($data['token'])) {
                    throw new \Exception('Cannot retrieve raidar token');
                }
                return $data['token'];
            },
            300
        );
    }
}
