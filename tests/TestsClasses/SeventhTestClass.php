<?php

namespace NJContainer\Tests\TestsClasses;

class SeventhTestClass
{
    protected $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * Get the value of params.
     */
    public function getParams()
    {
        return $this->params;
    }
}
