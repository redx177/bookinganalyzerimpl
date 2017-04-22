<?php

class Runtime
{
    private $startTime;
    private $lastTick;

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->tick();
    }

    public function fromBeginning()
    {
        return microtime(true) - $this->startTime;
    }

    public function fromLastTick() {
        return microtime(true) - $this->lastTick;
    }

    public function tick() {
        $this->lastTick = microtime(true);
    }
}