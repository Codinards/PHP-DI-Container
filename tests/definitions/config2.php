<?php

use function NJContainer\Container\add;
use function NJContainer\Container\get;

use NJContainer\Tests\TestsClasses\FactoryTestClass;
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
        [2, 'un tableau', get(Route::class)]
        ]),

    'testAddHelper3' => add([
            'toi',
            'lui',
            function () {
                return 'Un bonjour de coeur';
            },
            get(Route::class),
            [2, 'un tableau', get(Route::class)]
            ]),
    'testAddHelper4' => add(
        [get(Route::class)]
    ),
    FourthTestClass::class => get()->setParameters(
        [
            'undefine1' => get(FirstTestClass::class),
            'undefine2' => get(SecondTestClass::class)
        ]
    ),

    FifthTestClass::class => function (Request $request) {
        return new FifthTestClass($request->getRoute());
    },

    SixthTestClass::class => get()->setParameter('route', get(Route::class)),
    FactoryTestClass::class => get()
];
