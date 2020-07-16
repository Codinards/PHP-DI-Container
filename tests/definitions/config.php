<?php

use function NJContainer\Container\add;
use function NJContainer\Container\get;
use NJContainer\Tests\TestsClasses\FirstTestClass;
use NJContainer\Tests\TestsClasses\Request;
use NJContainer\Tests\TestsClasses\Response;
use NJContainer\Tests\TestsClasses\Route;
use NJContainer\Tests\TestsClasses\SecondTestClass;
use NJContainer\Tests\TestsClasses\SeventhTestClass;
use NJContainer\Tests\TestsClasses\ThirdTestClass;
use Psr\Container\ContainerInterface;

return [
    /* We are using NJContainer\add() to a depency key which is not yet define */
    Response::class => add(
        get(Response::class)
    )->setParameters([
        'method' => 'get',
        'uri'    => '/'
    ]),

    ThirdTestClass::class => function (ContainerInterface $container) {
        return new ThirdTestClass(
            $container->get(FirstTestClass::class),
            $container->get(SecondTestClass::class)
        );
    },

    'undefine1' => [
        'bonjour',
        Request::class,
        get(FirstTestClass::class),
        345,
        [
            'name'  => Route::class,
            'other' => get(SecondTestClass::class),
            'array' => [
                get(Request::class),
                function () {
                    return 'The closure is resolved';
                }
            ]
        ]
    ],

    'testAddHelper' => 'moi',
    'testAddHelper2' => get(Request::class),
    'testAddHelper3' => ['moi'],
    'sevenTestClass.params' => [
        get(Route::class),
        get(FirstTestClass::class)
    ],

    SeventhTestClass::class => get()
        ->setParameter('params', get('sevenTestClass.params'))
];
