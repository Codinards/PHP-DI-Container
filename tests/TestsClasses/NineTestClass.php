<?php

namespace NJContainer\Tests\TestsClasses;

class NineTestClass
{
    protected $eigthTestClass;

    public function __construct(EigthTestClass $eigthTestClass)
    {
        $this->eigthTestClass = $eigthTestClass;
    }

    /**
     * Get the value of eigthTestClass
     */
    public function getEigthTestClass()
    {
        return $this->eigthTestClass;
    }
}
