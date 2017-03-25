<?php

class AprioriAlgorithm
{
    private $bookingsProvider;
    private $bookingsCount;

    /**
     * AprioriAlgorithm constructor.
     * @param BookingsProvider $bookingsProvider Provider for the data to analyze.
     * @param ConfigProvider $config Configuration provider.
     */
    public function __construct(BookingsProvider $bookingsProvider, ConfigProvider $config)
    {
        $this->bookingsProvider = $bookingsProvider;
        $this->minSup = $config->get('aprioriMinSup');
    }

    /**
     * Analyzes the bookings with the apriori algorithm.
     * @param Filters|null $filters Filter set for the bookings.
     * @return Histograms Histograms representing the results.
     */
    public function run(Filters $filters = null) : Histograms
    {
        $candidates = $this->getCandidates1($filters);
        $frequentSets = [];
        $frequentSets[0] = $this->filterByMinSup($candidates);
        return $this->generateHistograms($frequentSets, $this->bookingsCount);
    }

    private function getCandidates1(Filters $filters = null): array
    {
        $c1 = [];
        $offset = 0;
        $batchSize = 1000;
        $bookingsCount = 0;
        while (!$this->bookingsProvider->hasEndBeenReached()) {
            $bookings = $this->bookingsProvider->getSubset($offset, $batchSize, $filters);
            foreach ($bookings as $booking) {
                $bookingsCount++;
                foreach ($booking->getBooleanFields() as $key => $value) {
                    if ($value) {
                        if (!array_key_exists($key . $value, $c1)) {
                            $c1[$key . $value] = [$key, $value, 0];
                        }
                        $c1[$key . $value] = [$key, $value, $c1[$key . $value][2]+1];
                    }
                }
                foreach ($booking->getIntegerFields() as $key => $value) {
                    if (!array_key_exists($key . $value, $c1)) {
                        $c1[$key . $value] = [$key, $value, 0];
                    }
                    $c1[$key . $value] = [$key, $value, $c1[$key . $value][2]+1];
                }
                foreach ($booking->getDistanceFields() as $key => $value) {
                    if ($value != Distance::Empty) {
                        if (!array_key_exists($key . $value, $c1)) {
                            $c1[$key . $value] = [$key, $value, 0];
                        }
                        $c1[$key . $value] = [$key, $value, $c1[$key . $value][2]+1];
                    }
                }
                foreach ($booking->getPricefields() as $key => $value) {
                    if ($value != Price::Empty) {
                        if (!array_key_exists($key . $value, $c1)) {
                            $c1[$key . $value] = [$key, $value, 0];
                        }
                        $c1[$key . $value] = [$key, $value, $c1[$key . $value][2]+1];
                    }
                }
            }
        }
        $this->bookingsCount = $bookingsCount;
        return $c1;
    }

    private function filterByMinSup($candidates)
    {
        $frequentSet = [];
        foreach ($candidates as $key => $value) {
            if ($value[2] >= $this->minSup) {
                $frequentSet[$key] = $value;
            }
        }
        return $frequentSet;
    }

    private function generateHistograms($frequentSets, $bookingsCount)
    {
        $setSize = 1;
        $histograms = new Histograms();
        foreach ($frequentSets as $frequentSet) {
            $histogram = new Histogram();
            foreach ($frequentSet as $key => $value) {
                $histogram->addHistogramBin(new HistogramBin($value[0], $value[1], $value[2], $bookingsCount));
            }
            $histograms->addHistogram($setSize, $histogram);
            $setSize++;
        }

        return $histograms;
    }
}