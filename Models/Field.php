<?php

interface Field
{
    public function getName();
    public function getValue();
    public function hasValue();
    public static function getType();
}