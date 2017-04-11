<?php

Interface Random
{
    /**
     * Generates a random number between 0 and $max.
     * @param int $max Maxiumum number
     * @return int Randomly generated number.
     */
    public function generate(int $max): int;
}