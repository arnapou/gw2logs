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

use App\Log;
use App\Logger\ProcessLogger;
use App\LogMetadata;
use App\Utils;

class Gw2RaidarUrlProcessing extends AbstractProcessing
{
    use Gw2RaidarTrait;

    /**
     * @return string
     */
    public function getTagName()
    {
        return LogMetadata::TAG_GW2RAIDARURL;
    }

    /**
     * @param Log $log
     * @throws \Exception
     */
    public function process(Log $log)
    {
        if (!$log->metadata()->hasTag(LogMetadata::TAG_GW2RAIDAR)) {
            throw new \Exception('Not already uploaded');
        }

        foreach ($this->getEncounters() as $encounter) {
            if ($log->isSameDateTimeAs($encounter['filename'])) {
                $log->metadata()->setUrlRaidar(GW2RAIDAR_URL . 'encounter/' . $encounter['url_id'])->save();
                return;
            }
        }

        $this->resetGw2raidarTagIfItWasProcessedALongTimeAgo($log);

        throw new \Exception('Url not found on raidar', ProcessLogger::CODE_NOTICE);
    }

    /**
     * @return array
     */
    public function getEncounters()
    {
        return Utils::cached(
            'gw2raidarurl_encounters',
            function () {
                $data = Utils::curl(
                    'GET',
                    GW2RAIDAR_URL . 'api/v2/encounters?limit=1000',
                    null,
                    $this->getAuthorizationHeaders()
                );
                return $data['results'] ?? [];
            },
            300
        );
    }

    /**
     *
     * It will force the process to upload it again in case there was a problem
     *
     * @param Log $log
     */
    private function resetGw2raidarTagIfItWasProcessedALongTimeAgo(Log $log)
    {
        $gw2raidarProcessedTime = $log->metadata()->get('processed_' . LogMetadata::TAG_GW2RAIDAR);
        if (time() - $gw2raidarProcessedTime > 6 * 3600) {
            $log->metadata()->removeTag(LogMetadata::TAG_GW2RAIDAR)->save();
        }
    }
}
