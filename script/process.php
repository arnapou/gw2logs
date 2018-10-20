<?php

use App\Log;
use App\LogMetadata;
use App\Processing\DpsReportProcessing;
use App\Processing\Gw2RaidarProcessing;
use App\Processing\Gw2RaidarUrlProcessing;
use App\ProcessingStack;

require __DIR__ . '/../vendor/autoload.php';

$dateLimit = new DateTimeImmutable('@' . (time() - PROCESS_MAX_EXECUTION_TIME));

$processors = [
    LogMetadata::TAG_DPSREPORT    => new ProcessingStack(new DpsReportProcessing()),
    LogMetadata::TAG_GW2RAIDAR    => new ProcessingStack(new Gw2RaidarProcessing()),
    LogMetadata::TAG_GW2RAIDARURL => new ProcessingStack(new Gw2RaidarUrlProcessing()),
];

foreach (Log::all() as $log) {
    $metadata = $log->metadata();

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

    foreach ($processor->errors() as $error) {
        echo implode("\t ", [
                    $error['log']->filename(),
                    $processor->getProcessing()->getTagName(),
                    $error['exception']->getMessage(),
                ]
            ) . "\n";
    }
}
