<?php

/*
 * This file is part of the Bizmuth Bot project
 *
 * (c) Antoine Bluchet <antoine@bluchet.fr>
 * (c) Lemay Marc <flugv1@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Drift;

use Drift\HttpKernel\AsyncKernel;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * Class Kernel.
 */
class Kernel extends AsyncKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        $contents = require $this->getApplicationLayerDir().'/../config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class($this);
            }
        }
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }

    private function getApplicationLayerDir(): string
    {
        return $this->getProjectDir().'/Drift';
    }

    /**
     * @throws \Exception
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $confDir = $this->getApplicationLayerDir().'/../config';
        $loader->load($confDir.'/services.php');
    }

    /**
     * @throws LoaderLoadException
     */
    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = $this->getApplicationLayerDir().'/../config';
        $routes->import($confDir.'/routes.yml');
    }
}
