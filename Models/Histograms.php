<?php

class Histograms
{
    private $histograms = [];

    public function addHistogram(Histogram $histogram) {
        $this->histograms[] = $histogram;
    }

    public function getHistogram(int $setSize) : ?Histogram {
        foreach ($this->histograms as $histogram) {
            if ($setSize == $histogram->getSetSize()) {
                return $histogram;
            }
        }
        return null;
    }

    public function getAll() : array {
        return $this->histograms;
    }

    public function getTotal() : int {
        if (count($this->histograms) == 0) {
            return 0;
        }
        $histogramBins = $this->histograms[1]->getHistogramBins();

        if (count($histogramBins) == 0) {
            return 0;
        }
        return $histogramBins[0]->getTotal();
    }
}