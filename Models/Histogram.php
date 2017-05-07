<?php

class Histogram
{
    /**
     * @var HistogramBin[]
     */
    private $histogramBins = [];
    private $setSize;

    /**
     * Histogram constructor.
     * @param int $setSize Set size of the fields in the histogram bins.
     */
    public function __construct(int $setSize)
    {
        $this->setSize = $setSize;
    }

    public function getSetSize(): int
    {
        return $this->setSize;
    }

    public function addHistogramBin(HistogramBin $histogramBin)
    {
        array_push($this->histogramBins, $histogramBin);
    }

    public function getHistogramBins() {
        return $this->histogramBins;
    }
}