<?php


namespace App;


use Traversable;

class LogList implements \IteratorAggregate
{
    /**
     * @var Log[]
     */
    private $logs;
    /**
     * @var int
     */
    private $offset;
    /**
     * @var int
     */
    private $length;
    /**
     * @var int
     */
    private $pageNum;
    /**
     * @var int
     */
    private $pageCount;
    /**
     * @var array
     */
    private $filtres;

    /**
     * LogList constructor.
     * @param array $logs
     * @param int   $offset
     * @param int   $length
     * @param array $filtres
     */
    public function __construct(array $logs, $offset, $length, $filtres)
    {
        $this->logs      = $logs;
        $this->offset    = $offset;
        $this->length    = $length ?: 100;
        $this->pageNum   = floor($this->offset / $this->length) + 1;
        $this->pageCount = ceil(count($this->logs) / $this->length);
        $this->filtres   = $filtres;
    }

    /**
     * @return Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator(array_slice($this->logs, $this->offset, $this->length));
    }

    /**
     * @return int
     */
    public function pageNum()
    {
        return $this->pageNum;
    }

    /**
     * @return int
     */
    public function pageCount()
    {
        return $this->pageCount;
    }

    /**
     * @return int
     */
    public function offset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function length()
    {
        return $this->length;
    }

    /**
     * @return array
     */
    public function getFiltres()
    {
        return $this->filtres;
    }

    /**
     * @param $num
     * @return array
     */
    public function getFiltre($num)
    {
        return $this->filtres[$num] ?? '';
    }


}