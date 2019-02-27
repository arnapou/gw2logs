<?php

namespace App\Processing;

use App\Log;
use App\LogMetadata;
use App\Utils;

class Gw2RaidarProcessing extends AbstractProcessing
{
    use Gw2RaidarTrait;

    /**
     * @return string
     */
    public function getTagName()
    {
        return LogMetadata::TAG_GW2RAIDAR;
    }

    /**
     * @param Log $log
     * @throws \Exception
     */
    public function process(Log $log)
    {
        $data = Utils::curl(
            'PUT',
            GW2RAIDAR_URL . 'api/v2/encounters/new',
            ['file' => curl_file_create($log->pathname())],
            $this->getAuthorizationHeaders()
        );

        if (!isset($data['upload_id'])) {
            throw new \Exception('No upload_id in response');
        }

        Utils::writeJson($log->path() . '/gw2raidar.json', $data);
    }
}
