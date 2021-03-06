<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing\Loader\PhpFileLoader as RoutingLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

$fileLocator = new FileLocator();
$containerBuilder = new ContainerBuilder();

$loader = new PhpFileLoader($containerBuilder, $fileLocator);
$loader->load(dirname(__DIR__) . '/config/services.php');

$containerBuilder->compile();

$routingLoader = new RoutingLoader($fileLocator);
$matcher = new UrlMatcher($routingLoader->load(dirname(__DIR__) . '/config/routes.php'), new RequestContext());

$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack()));

$controllerResolver = new ContainerControllerResolver($containerBuilder);
$argumentResolver = new ArgumentResolver();
