<?php

/*
 * This file is part of the Arnapou gw2logs package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use App\Log;
use App\Logger\ProcessLogger;
use App\LogMetadata;
use App\Processing\DpsReportProcessing;
use App\Processing\Gw2RaidarProcessing;
use App\Processing\Gw2RaidarUrlProcessing;
use App\ProcessingStack;

require __DIR__ . '/../vendor/autoload.php';

$dateLimit      = new DateTimeImmutable('@' . (time() - PROCESS_MAX_EXECUTION_TIME));
$dateDeleteFail = new DateTimeImmutable('@' . (time() - FAIL_LOG_MAX_RETENTION));
$dateDeleteKill = new DateTimeImmutable('@' . (time() - KILL_LOG_MAX_RETENTION));
$logger         = new ProcessLogger();

$processors = [
    LogMetadata::TAG_DPSREPORT    => new ProcessingStack(new DpsReportProcessing(), $logger),
    LogMetadata::TAG_GW2RAIDAR    => new ProcessingStack(new Gw2RaidarProcessing(), $logger),
    LogMetadata::TAG_GW2RAIDARURL => new ProcessingStack(new Gw2RaidarUrlProcessing(), $logger),
];

foreach (Log::all() as $log) {
    /** @var Log $log */
    $metadata = $log->metadata();

    if ($metadata->getStatus() === LogMetadata::STATUS_FAIL && $metadata->lastModified() < $dateDeleteFail ||
        $metadata->getStatus() === LogMetadata::STATUS_FAIL && $metadata->encounterTime() < $dateDeleteFail ||
        $metadata->getStatus() === LogMetadata::STATUS_KILL && $metadata->lastModified() < $dateDeleteKill ||
        $metadata->getStatus() === LogMetadata::STATUS_KILL && $metadata->encounterTime() < $dateDeleteKill
    ) {
        $log->delete();
        $logger->error('Delete expired file', [$log->filename(), 'process']);
        continue;
    }

    if (
        $metadata->hasTag(LogMetadata::TAG_PROCESSING) && $metadata->lastModified() > $dateLimit ||
        $metadata->hasTag(LogMetadata::TAG_DISABLED)
    ) {
        // skip currently processing log
        continue;
    }

    foreach ($processors as $tag => $processor) {
        if (!$metadata->hasTag($tag)) {
            $processor->add($log);
        }
    }
}

/** @var ProcessingStack[] $processors */
foreach ($processors as $processor) {
    $processor->process();
}
