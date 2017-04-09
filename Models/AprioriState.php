<?php

/**
 * Created by PhpStorm.
 * User: slang
 * Date: 09.04.17
 * Time: 14:43
 */
class AprioriState
{
    /**
     * @var array
     */
    private $frequentSets;
    /**
     * @var array|null
     */
    private $sortedSlicedCandidates;
    /**
     * @var int
     */
    private $bookingsCount;
    /**
     * @var mixed
     */
    private $fieldNameMapping;
    /**
     * @var mixed
     */
    private $runtime;
    private $done;
    private $candidatesCount;

    /**
     * AprioriState constructor.
     * @param array $frequentSets
     * @param array|null $candidates
     * @param int $bookingsCount
     * @param array $fieldNameMapping
     * @param float $runtime
     */
    public function __construct(array $frequentSets, $candidates, int $bookingsCount, array $fieldNameMapping, float $runtime)
    {
        $this->frequentSets = $frequentSets;
        $this->candidates = $candidates;
        $this->candidatesCount = count($candidates);
        $this->done = $candidates === null;
        $this->bookingsCount = $bookingsCount;
        $this->fieldNameMapping = $fieldNameMapping;
        $this->runtime = $runtime;
    }
}