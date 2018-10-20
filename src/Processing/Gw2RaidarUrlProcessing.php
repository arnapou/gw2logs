<?php

namespace App\Processing;

use App\Log;
use App\LogMetadata;
use App\Utils;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\PhpFilesCache;

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

        throw new \Exception('Url not found on raidar');
    }

    /**
     * @return array
     */
    private function getEncounters()
    {
        return Utils::cached(
            'gw2raidarurl_encounters',
            function () {
                $data = Utils::curl(
                    'GET',
                    GW2RAIDAR_URL . "api/v2/encounters?limit=1000",
                    null,
                    $this->getAuthorizationHeaders()
                );
                return $data['results'] ?? [];
            }, 300);
    }
}