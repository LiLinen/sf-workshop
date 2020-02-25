<?php
declare(strict_types=1);

namespace App;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\PhpFileLoader as RoutingLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class Kernel extends BaseKernel
{
    /** @var FileLocator */
    private $fileLocator;

    /** @var ContainerBuilder */
    protected $container;

    /** @var PhpFileLoader */
    private $loader;

    /** @var RoutingLoader */
    private $routingLoader;

    /** @var EventDispatcher */
    private $dispatcher;

//    /** @var HttpKernel */
//    private $kernel;

    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);

        $this->fileLocator = new FileLocator();
        $this->container = new ContainerBuilder();
        $this->loader = new PhpFileLoader($this->container, $this->fileLocator);
        $this->routingLoader = new RoutingLoader($this->fileLocator);
        $this->dispatcher = new EventDispatcher();
//        $this->kernel = new HttpKernel(
//            $this->dispatcher,
//            new ContainerControllerResolver($this->container),
//            new RequestStack(),
//            new ArgumentResolver()
//        );
    }

    public function run(): void
    {
        $this->loadServices();
        $this->loadRoutes();

        $request = Request::createFromGlobals();

        try {
            $response = $this->handle($request);
        } catch (\Throwable $e) {
            dump($e);
            exit;
        }

        $response->send();
        $this->terminate($request, $response);
    }

    private function loadServices(): void
    {
        $this->loader->load($this->getProjectDir() . '/config/services.php');
        $this->container->compile();
    }

    private function loadRoutes(): void
    {
        $matcher = new UrlMatcher(
            $this->routingLoader->load($this->getProjectDir() . '/config/routes.php'),
            new RequestContext()
        );

        $this->dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack()));
    }


    /**
     * @inheritDoc
     */
    public function registerBundles()
    {
        $contents = require $this->getProjectDir() . '/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // todo
    }

    /**
     * @return string
     */
    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

//    private function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
//    {
//    }
}
