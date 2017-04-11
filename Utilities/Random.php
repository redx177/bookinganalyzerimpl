<?php

/**
 * Created by PhpStorm.
 * User: slang
 * Date: 11.04.17
 * Time: 22:29
 */
class Randomizer implements Random
{
    public function generate(int $max): int
    {
        return mt_rand(0, $max);
    }
}