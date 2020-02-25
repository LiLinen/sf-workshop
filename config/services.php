<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
    ;

    $services->load('App\\', '../src/*')
        ->exclude('../src/{DependencyInjection,Entity,Migrations,Tests,Kernel.php}');


    $services->set('http_kernel', HttpKernel::class)
        ->args(
            [
                ref('debug.event_dispatcher'),
                ref('debug.controller_resolver'),
                ref('request_stack'),
                ref('debug.argument_resolver'),
            ]
        );
//        ->public();
    $services->set('debug.event_dispatcher', EventDispatcher::class);
    $services->set('debug.controller_resolver', ControllerResolver::class);
    $services->set('request_stack', RequestStack::class);
    $services->set('debug.argument_resolver', ArgumentResolver::class);
};
