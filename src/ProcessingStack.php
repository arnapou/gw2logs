<?php


namespace App;


use App\Processing\AbstractProcessing;

class ProcessingStack
{
    const KEY_FIRST_PROCESSING = 'first_processing';
    const KEY_INTERVAL = 'interval_processing';
    const KEY_TIME_NEXT = 'next_processing';
    /**
     * @var AbstractProcessing
     */
    private $processing;
    /**
     * @var Log[]
     */
    private $logs = [];
    /**
     * @var array
     */
    private $errors = [];

    /**
     * ProcessingStack constructor.
     * @param AbstractProcessing $processing
     */
    public function __construct(AbstractProcessing $processing)
    {
        $this->processing = $processing;
    }

    /**
     * @return AbstractProcessing
     */
    public function getProcessing()
    {
        return $this->processing;
    }

    /**
     * @param Log $log
     */
    public function add(Log $log)
    {
        $metadata = $log->metadata();
        if (!$metadata->hasTag(LogMetadata::TAG_PROCESSING)) {
            $metadata->addTag(LogMetadata::TAG_PROCESSING)->save();
        }

        $firstProcessing = $metadata->get(self::KEY_FIRST_PROCESSING);
        if (empty($firstProcessing)) {
            $metadata->set(self::KEY_FIRST_PROCESSING, time())->save();
        }

        $this->logs[] = $log;
    }

    /**
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     *
     */
    public function process()
    {
        foreach ($this->logs as $log) {
            try {
                if ($this->isNextProcessingReached($log->metadata())) {
                    $this->processing->process($log);
                    $log->metadata()->addTag($this->processing->getTagName())->save();
                }
            } catch (\Exception $exception) {
                $this->errors[] = ['log' => $log, 'exception' => $exception];
                $this->errorProcessing($log);
            }
        }
    }

    private function errorProcessing(Log $log)
    {
        $metadata = $log->metadata();

        if ($this->isProcessingTooOld($metadata)) {
            if (\count($metadata->getTags()) <= 1) {
                $log->delete(); // There was no processing at all
            } else {
                $metadata->addTag(LogMetadata::TAG_DISABLED);
            }
        } else {
            $interval = $metadata->get(self::KEY_INTERVAL, 0) + PROCESS_INTERVAL_INCREMENT;
            $interval = $interval > PROCESS_INTERVAL_MAXIMUM ? PROCESS_INTERVAL_MAXIMUM : $interval;
            $metadata->set(self::KEY_INTERVAL, $interval);
            $metadata->set(self::KEY_TIME_NEXT, time() + $interval);
        }
    }

    public function __destruct()
    {
        foreach ($this->logs as $log) {
            if ($log->metadata()->hasTag(LogMetadata::TAG_PROCESSING)) {
                $log->metadata()->removeTag(LogMetadata::TAG_PROCESSING)->save();
            }
        }
    }

    /**
     * @param $metadata
     * @return bool
     */
    private function isNextProcessingReached($metadata)
    {
        return time() >= $metadata->get(self::KEY_TIME_NEXT, 0);
    }

    /**
     * @param $metadata
     * @return bool
     */
    private function isProcessingTooOld($metadata)
    {
        return time() - $metadata->get(self::KEY_FIRST_PROCESSING) > PROCESS_TTL_BEFORE_DISABLED;
    }

}