<?php

class Randomizer implements Random
{
    public function generate(int $max): int
    {
        return mt_rand(1, $max);
    }
}