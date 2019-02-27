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
use App\LogMetadata;
use App\Utils;

class DpsReportProcessing extends AbstractProcessing
{
    /**
     * @return string
     */
    public function getTagName()
    {
        return LogMetadata::TAG_DPSREPORT;
    }

    /**
     * @param Log $log
     */
    public function process(Log $log)
    {
        $data = Utils::curl(
            'POST',
            DPSREPORT_URL . 'uploadContent?json=1&generator=ei&userToken=' . DPSREPORT_TOKEN,
            ['file' => curl_file_create($log->pathname())]
        );

        if (!empty($data['error'])) {
            throw new \RuntimeException($data['error']);
        } elseif (!isset($data['permalink'], $data['encounterTime'], $data['encounter'], $data['players'])) {
            throw new \RuntimeException('Json format seems invalid');
        } else {
            $this->saveData($log, $data);
        }
    }

    /**
     * @param Log   $log
     * @param array $data
     */
    private function saveData(Log $log, $data)
    {
        $log->metadata()
            ->setPlayers(array_values($data['players']))
            ->setEncounterTime($data['encounterTime'])
            ->setUrlDpsReport($data['permalink'])
            ->setBoss($data['encounter']['boss'] ?? '?')
            ->setBossId($data['encounter']['bossId'] ?? '?')
            ->setStatus(($data['encounter']['success'] ?? false) ? LogMetadata::STATUS_KILL : LogMetadata::STATUS_FAIL);

        Utils::writeJson($log->path() . '/dpsreport.json', $data);
    }
}
