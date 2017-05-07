<?php
use \DI\FactoryInterface;

class AprioriAlgorithm
{
    private $bookingsCountCap;
    private $lastOutput;
    private $outputFile;
    private $outputInterval;
    private $startTime;
    private $fieldNameMapping;
    private $rootDir;
    /**
     * @var AprioriProgress
     */
    private $progress;
    /**
     * @var BookingDataIterator
     */
    private $bookingDataIterator;
    /**
     * @var FactoryInterface
     */
    private $factory;

    public function __construct(BookingDataIterator $bookingDataIterator,
                                ConfigProvider $config,
                                AprioriProgress $progress,
                                FactoryInterface $factory)
    {
        $this->progress = $progress;
        $this->bookingDataIterator = $bookingDataIterator;
        $this->factory = $factory;

        $this->lastOutput = microtime(TRUE);
        $this->startTime = microtime(TRUE);

        $this->bookingsCountCap = $config->get('bookingsCountCap');
        $this->fieldNameMapping = $config->get('fieldNameMapping');
        $this->rootDir = $config->get('rootDir');

        $aprioriConfig = $config->get('apriori');
        $this->minSup = $aprioriConfig['minSup'];
        $this->stopFile = $aprioriConfig['serviceStopFile'];
        $this->outputInterval = $aprioriConfig['outputInterval'];
        $this->outputFile = $aprioriConfig['serviceOutput'];
    }

    /**
     * Analyzes the bookings with the apriori algorithm and a file as datasource.
     * @return Histograms Histograms representing the results.
     */
    public function run() : Histograms
    {
        $frequentSets = [];
        $frequentSets[0] = $this->getInitialFrequentSets();
        for ($i = 1; true && !$this->isStopScriptSet(); $i++) {
            $candidates = $this->aprioriGen($frequentSets[$i-1]);
            if (!$candidates) {
                break;
            }
            $countedCandidates = $this->countCandidates($candidates, $frequentSets);
            $frequentSet = $this->filterByMinSup($countedCandidates, $this->bookingDataIterator->count());
            usort($frequentSet, array('AprioriAlgorithm', 'frequentSetSort'));
            $frequentSets[$i] = $frequentSet;
        }
        $this->storeState(null, $frequentSets);
        return $this->generateHistograms($frequentSets, $this->bookingDataIterator->count());
    }


    /**
     * Analyzes the bookings with the apriori algorithm and a provided Clusters as datasource.
     * @param KPrototypeResult $clusters Clusters to analyze. Each Cluster will be anaylzed with the apriori algorithm.
     * @return KPrototypeResult with attached histograms.
     */
    public function runWithClusters(ClusteringResult $clusters): ClusteringResult {
        foreach ($clusters->getClusters() as $cluster) {
            $this->bookingDataIterator = $this->factory->make(
                BookingDataIterator::class, [
                    'dataIterator' =>
                        $this->factory->make(LoadClusterDataIterator::class, ['cluster' => $cluster])
            ]);
            $this->storeClusterState($clusters, 1, $cluster);
            $cluster->setHistograms($this->run());
        }
        $this->storeClusterState($clusters, 2);
        return $clusters;
    }

    private function getInitialFrequentSets(): array
    {
        /** @var Field[] $fields */
        $candidates = [];
        $offset = 0;
        foreach ($this->bookingDataIterator as $booking) {
            if ($this->bookingsCountCap && $offset >= $this->bookingsCountCap) {
                break;
            }
            $offset++;

            $fields = array_merge(
                $booking->getFieldsByType(BooleanField::class),
                $booking->getFieldsByType(IntegerField::class),
                $booking->getFieldsByType(DistanceField::class),
                $booking->getFieldsByType(PriceField::class));

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

            if ($offset % 1000 == 0) {
                $this->storeState($candidates);
            }

        }

        $frequentSet = $this->filterByMinSup($candidates, $this->bookingDataIterator->count());
        usort($frequentSet, array('AprioriAlgorithm', 'frequentSetSort'));
        return $frequentSet;
    }

    private function countCandidates($candidates, $frequentSets): array
    {
        $countedCandidates = [];
        $offset = 0;
        foreach ($this->bookingDataIterator as $booking) {
            if ($this->bookingsCountCap && $offset >= $this->bookingsCountCap) {
                break;
            }
            $offset++;

            foreach ($candidates as $candidate) {
                $id = [];
                $c = [];
                foreach ($candidate as $key => $value) {
                    $field = $booking->getFieldByName($key);
                    if (!$field->hasValue() || $field->getValue() != $value) {
                        // This candidate does not match this booking, so go to next candidate.
                        continue 2;
                    }
                    $id[$key] = $value;
                    $c[$key] = $value;
                }

                $k = $this->getUniqueKey($id);

                if (!array_key_exists($k, $countedCandidates)) {
                    $countedCandidates[$k] = [$c, 0];
                }
                $countedCandidates[$k] = [$c, $countedCandidates[$k][1]+1];
            }
            if ($offset % 1000 == 0) {
                $this->storeState($countedCandidates, $frequentSets);
            }
        }
        $this->bookingDataIterator->rewind();
        return $countedCandidates;
    }

    private function filterByMinSup(array $candidates, $bookingsCount)
    {
        $frequentSet = [];
        foreach ($candidates as $key => $value) {
            if ($value[1] > ceil($bookingsCount * $this->minSup)) {
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
                        $k = $this->getUniqueKey($candidate);
                        $candidates[$k] = $candidate;
                    }
                }
            }
        }
        return $candidates;
    }

    private function storeState($candidates = null, $frequentSets = null)
    {
        $this->progress->storeState($this->startTime, $this->bookingDataIterator->count(), $candidates, $frequentSets);
    }

    private function storeClusterState(ClusteringResult $clusters, $status, Cluster $cluster = null)
    {
        $this->progress->storeClusterState($clusters, $status, $cluster);
        // If status is done, store the final state.
        if ($status == 2) {
            $this->storeState();
        }
    }

    static function frequentSetSort($a, $b) {
        if ($a[1] == $b[1]) {
            return 0;
        }
        return $a[1] > $b[1] ? -1 : 1;
    }

    private function isStopScriptSet()
    {
        return file_exists($this->rootDir . $this->stopFile);
    }

    /**
     * Generates an unique key for the array.
     * Order of the elements does not matter.
     * Sorts the array by key first so [k1=>v1,k2=>v2] and [k2=>v2,k1=>v1] generate the same key.
     * @param array $array Array to create the unique key for.
     * @return string Unique key.
     */
    private function getUniqueKey($array): string
    {
        ksort($array);
        return implode(array_keys($array)) . implode(array_values($array));
    }

    /**
     * Gets the amount of bookings.
     * @return int Bookings count.
     */
    public function getBookingsCount()
    {
        return $this->bookingDataIterator->count();
    }
}