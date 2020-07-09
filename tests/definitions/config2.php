<?php

use function NJContainer\add;
use function NJContainer\get;
use NJContainer\Tests\TestsClasses\FifthTestClass;
use NJContainer\Tests\TestsClasses\FirstTestClass;
use NJContainer\Tests\TestsClasses\FourthTestClass;
use NJContainer\Tests\TestsClasses\Request;
use NJContainer\Tests\TestsClasses\Route;
use NJContainer\Tests\TestsClasses\SecondTestClass;
use NJContainer\Tests\TestsClasses\SixthTestClass;

return [
    'testAddHelper' => add([
        'toi',
        'lui',
        function () {
            return 'Un bonjour de coeur';
        },
        get(Route::class),
        [
            2,
            'un tableau',
            get(Route::class)
        ]
        ]),

    FourthTestClass::class => get()->setParameters(
        [
            'undefine1' => get(FirstTestClass::class),
            'undefine2' => get(SecondTestClass::class)
        ]
    ),

    FifthTestClass::class => function (Request $request) {
        return new FifthTestClass($request->getRoute());
    },

    SixthTestClass::class => get()->setParameter('route', get(Route::class))
];
