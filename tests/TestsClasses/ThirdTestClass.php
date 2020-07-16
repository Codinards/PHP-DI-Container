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

    /**
     * Get the value of secondtestClass
     */
    public function getSecondtestClass()
    {
        return $this->secondtestClass;
    }

    /**
     * Get the value of firstTestClass
     */
    public function getFirstTestClass()
    {
        return $this->firstTestClass;
    }
}
