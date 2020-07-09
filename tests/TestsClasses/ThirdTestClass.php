<?php

namespace NJContainer\Tests\TestsClasses;

class ThirdTestClass
{
    protected $firstTestClass;

    protected $secondtestClass;

    public function __construct(FirstTestClass $firstTestClass, SecondTestClass $secondtestClass)
    {
        $this->firstTestClass = $firstTestClass;
        $this->secondtestClass = $secondtestClass;
    }
}
