<?php

class AprioriAlgorithm
{
    private $bookingsProvider;
    private $bookingsCount;
    private $bookingsCountCap;
    private $lastOutput;
    private $outputFile;
    private $outputInterval;
    private $fileWriteCount = 0;
    private $startTime;
    private $fieldNameMapping;
    private $rootDir;

    /**
     * @var Twig_TemplateWrapper
     */
    private $template;

    /**
     * AprioriAlgorithm constructor.
     * @param BookingsProvider $bookingsProvider Provider for the data to analyze.
     * @param ConfigProvider $config Configuration provider.
     * @param Twig_TemplateWrapper $template Template to render the wip to.
     */
    public function __construct(BookingsProvider $bookingsProvider, ConfigProvider $config, Twig_TemplateWrapper $template)
    {
        $this->bookingsProvider = $bookingsProvider;
        $this->minSup = $config->get('aprioriMinSup');
        $this->bookingsCountCap = $config->get('bookingsCountCap');
        $this->outputFile = $config->get('aprioriServiceOutput');
        $this->outputInterval = $config->get('aprioriOutputInterval');
        $this->fieldNameMapping = $config->get('fieldNameMapping');
        $this->rootDir = $config->get('rootDir');
        $this->lastOutput = microtime(TRUE);
        $this->startTime = microtime(TRUE);
        $this->template = $template;
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
            $countedCandidates = $this->countCandidates($candidates, $frequentSets, $filters);
            $frequentSets[$i] = $this->filterByMinSup($countedCandidates);
        }
        $this->writeOutput(null, $frequentSets);
        return $this->generateHistograms($frequentSets, $this->bookingsCount);
    }

    private function getInitialFrequentSets(Filters $filters = null): array
    {
        $candidates = [];
        $offset = 0;
        $batchSize = 1000;
        $bookingsCount = 0;
        while (!$this->bookingsProvider->hasEndBeenReached()) {
            if ($this->bookingsCountCap && $offset >= $this->bookingsCountCap) {
                break;
            }
            $bookings = $this->bookingsProvider->getSubset($batchSize, $filters);
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
                $this->writeOutput($candidates);
            }
            $offset += $batchSize;
        }
        $this->bookingsCount = $bookingsCount;
        return $this->filterByMinSup($candidates);
    }

    private function countCandidates($candidates, $frequentSets, Filters $filters = null): array
    {
        $countedCandidates = [];
        $offset = 0;
        $batchSize = 1000;
        $bookingsCount = 0;
        while (!$this->bookingsProvider->hasEndBeenReached()) {
            if ($this->bookingsCountCap && $offset >= $this->bookingsCountCap) {
                break;
            }
            $bookings = $this->bookingsProvider->getSubset($batchSize, $filters);
            $bookingsCount += count($bookings);
            foreach ($bookings as $booking) {
                foreach ($candidates as $candidate) {
                    $id = '';
                    $c = [];
                    foreach ($candidate as $key => $value) {
                        $field = $booking->getFieldByName($key);
                        if (!$field) {
                            // This candidate does not match this booking, so go to next candidate.
                            continue 2;
                        }
                        $name = $field->getName();
                        $value = $field->getValue();
                        $id .= $value . $name;
                        $c[$name] = $value;
                    }

                    if (!array_key_exists($id, $countedCandidates)) {
                        $countedCandidates[$id] = [$c, 0];
                    }
                    $countedCandidates[$id] = [$c, $countedCandidates[$id][1]+1];
                }
                $this->writeOutput($countedCandidates, $frequentSets);
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

    private function writeOutput($candidates = null, $frequentSets = null)
    {
        $currentTime = microtime(TRUE);
        if ($currentTime - $this->lastOutput > $this->outputInterval || $candidates == null) {
            $this->lastOutput = microtime(TRUE);
            $this->fileWriteCount++;
            $runtime = $currentTime - $this->startTime;

            // Take the top X candidates. Else there can be thousands of them.
            usort($candidates, array('AprioriAlgorithm', 'frequentSetSort'));
            $sortedSlicedCandidates = array_slice($candidates, 0, 10);

            $done = $candidates === null;
            // Sort frequentSets when done.
            if ($done) {
                $sortedFrequentSets = [];
                foreach ($frequentSets as $setSize => $frequentSet) {
                    usort($frequentSet, array('AprioriAlgorithm', 'frequentSetSort'));
                    $sortedFrequentSets[$setSize] = $frequentSet;
                }
                $frequentSets = $sortedFrequentSets;
            }

            $content = $this->template->render([
                'frequentSets' => $frequentSets,
                'candidates' => $sortedSlicedCandidates,
                'candidatesCount' => count($candidates),
                'fieldTitles' => $this->fieldNameMapping,
                'runtimeInSeconds' => $runtime,
                'done' => $done ? 'true' : 'false',
            ]);
            file_put_contents($this->rootDir . $this->outputFile, $content);
        }
    }

    static function frequentSetSort($a, $b) {
        if ($a[1] == $b[1]) {
            return 0;
        }
        return $a[1] > $b[1] ? -1 : 1;
    }
}