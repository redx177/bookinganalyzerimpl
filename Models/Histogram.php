<?php

class Histogram
{
    private $histogramBins = [];

    public function addHistogramBin(HistogramBin $histogramBin)
    {
        array_push($this->histogramBins, $histogramBin);
    }

    public function getHistogramBins() {
        return $this->histogramBins;
    }
}