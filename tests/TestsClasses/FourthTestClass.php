<?php

namespace NJContainer\Tests\TestsClasses;

class FourthTestClass
{
    protected $thirdTestClass;

    protected $undefine1;

    protected $undefine2;

    public function __construct(ThirdTestClass $thirdTestClass, $undefine1, $undefine2)
    {
        $this->thirdTestClass = $thirdTestClass;
        $this->undefine1 = $undefine1;
        $this->undefine2 = $undefine2;
    }
}
