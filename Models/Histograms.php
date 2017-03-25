<?php

class Histograms
{
    private $histograms = [];

    public function addHistogram(int $setSize, Histogram $histogram) {
        $this->histograms[$setSize] = $histogram;
    }

    public function getHistogram(int $setSize) : Histogram {
        if (!array_key_exists($setSize, $this->histograms)) {
            return null;
        }
        return $this->histograms[$setSize];
    }
}