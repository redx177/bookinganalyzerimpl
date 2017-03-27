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
        $frequentSets = [];
        $frequentSets[0] = $this->getInitialFrequentSets($filters);
        for ($i = 1; true; $i++) {
            $candidates = $this->aprioriGen($frequentSets[$i-1]);
            if (!$candidates) {
                break;
            }
            $countedCandidates = $this->countCandidates($candidates, $filters);
            $frequentSets[$i] = $this->filterByMinSup($countedCandidates);
        }
        return $this->generateHistograms($frequentSets, $this->bookingsCount);
    }

    private function getInitialFrequentSets(Filters $filters = null): array
    {
        $candidates = [];
        $offset = 0;
        $batchSize = 1000;
        $bookingsCount = 0;
        while (!$this->bookingsProvider->hasEndBeenReached()) {
            if ($offset >= 1000) {
                break;
            }
            $bookings = $this->bookingsProvider->getSubset($offset, $batchSize, $filters);
            $bookingsCount += count($bookings);
            foreach ($bookings as $booking) {
                $fields = array_merge(
                    $booking->getFieldsByType(bool::class),
                    $booking->getFieldsByType(int::class),
                    $booking->getFieldsByType(Distance::class),
                    $booking->getFieldsByType(Price::class));

                foreach ($fields as $field) {
                    if ($field->hasValue()) {
                        $name = $field->getName();
                        $value = $field->getValue();
                        if (!array_key_exists($name . $value, $candidates)) {
                            $candidates[$name . $value] = [[$name=>$value], 0];
                        }
                        $candidates[$name . $value] = [[$name=>$value], $candidates[$name . $value][1]+1];
                    }
                }
            }
            $offset += $batchSize;
        }
        $this->bookingsCount = $bookingsCount;
        return $this->filterByMinSup($candidates);
    }

    private function countCandidates($candidates, Filters $filters = null): array
    {
        $countedCandidates = [];
        $offset = 0;
        $batchSize = 1000;
        $bookingsCount = 0;
        while (!$this->bookingsProvider->hasEndBeenReached()) {
            if ($offset >= 1000) {
                break;
            }
            $bookings = $this->bookingsProvider->getSubset($offset, $batchSize, $filters);
            $bookingsCount += count($bookings);
            foreach ($bookings as $booking) {
                foreach ($candidates as $candidate) {
                    $fields = $booking->getFieldsByNamesAndValue($candidate);
                    if (count($fields) != count($candidate)) {
                        continue;
                    }
                    $id = '';
                    $c = [];
                    foreach ($fields as $field) {
                        if (!$field->hasValue()) {
                            continue 2;
                        }
                        $name = $field->getName();
                        $value = $field->getValue();
                        $id = $id . $name . $value;
                        $c[$name] = $value;
                    }

                    if (!array_key_exists($id, $countedCandidates)) {
                        $countedCandidates[$id] = [$c, 0];
                    }
                    $countedCandidates[$id] = [$c, $countedCandidates[$id][1]+1];
                }
            }
            $offset += $batchSize;
        }
        return $countedCandidates;
    }

    private function filterByMinSup(array $candidates)
    {
        $frequentSet = [];
        foreach ($candidates as $key => $value) {
            if ($value[1] >= $this->minSup) {
                $frequentSet[$key] = $value;
            }
        }
        return $frequentSet;
    }

    private function generateHistograms(array $frequentSets, int $bookingsCount)
    {
        $setSize = 1;
        $histograms = new Histograms();
        foreach ($frequentSets as $frequentSet) {
            if (count($frequentSet) === 0) {
                break;
            }

            $histogram = new Histogram($setSize);
            foreach ($frequentSet as $key => $value) {
                $histogram->addHistogramBin(new HistogramBin($value[0], $value[1], $bookingsCount));
            }
            $histograms->addHistogram($histogram);
            $setSize++;
        }

        return $histograms;
    }

    private function aprioriGen(array $oldFrequentSet)
    {
        $candidates = [];
        foreach ($oldFrequentSet as $frequentItem1) {
            $base = $frequentItem1[0];
            foreach ($oldFrequentSet as $frequentItem2) {
                $additionalItems = $frequentItem2[0];
                foreach ($additionalItems as $key => $additionalItem) {
                    $candidate = $base;
                    $candidate[$key] = $additionalItem;
                    if (count($candidate) == count($base)+1) {
                        // Generate unique key for the fields to prevent duplicates.
                        ksort($candidate);
                        $k = implode(array_keys($candidate)) . implode(array_values($candidate));

                        $candidates[$k] = $candidate;
                    }
                }
            }
        }
        return $candidates;
    }
}