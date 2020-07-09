<?php

namespace NJContainer\Tests\TestsClasses;

class Route
{
    protected $uniqId;

    public function __construct()
    {
        $this->uniqId = uniqid();
    }
}
