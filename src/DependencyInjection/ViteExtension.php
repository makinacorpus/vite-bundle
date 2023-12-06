<?php

declare(strict_types=1);

namespace MakinaCorpus\ViteBundle\DependencyInjection;

use MakinaCorpus\ViteBundle\Manifest\Manifest;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class ViteExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('services.yaml');

        $projectDir = $container->getParameter('kernel.project_dir');
        $environment = $container->getParameter('kernel.environment');
        $publicPath = $projectDir . '/public';

        $apps = [];
        foreach ($config['app'] ?? [] as $name => $data) {
            $manifestPath = $data['manifest'];

            list ($manifestPath) = Manifest::validateManifestFile($manifestPath, $publicPath);

            // What happens next belong to production environnement.
            if ('dev' !== $environment) {
                $entries = Manifest::parseManifestFile($manifestPath, $publicPath);
            } else {
                $entries = null;
            }

            if ($devServerUrl = $data['dev_url']) {
                if (!\preg_match('@^(http|https)://@', $devServerUrl)) {
                    throw new InvalidArgumentException("Vite dev serveur URL in 'vite.app.%s.dev_url' is not an HTTP or HTTPS URL.");
                }
            } else {
                $devServerUrl = null;
            }

            $serviceId = Manifest::class . '.' . $name;

            $definition = new Definition();
            $definition->setClass(Manifest::class);
            $definition->setArguments([$manifestPath, $publicPath, $entries, $devServerUrl]);
            $container->setDefinition($serviceId, $definition);

            $apps[$name] = new Reference($serviceId);
        }

        if ($apps) {
            $definition = $container->getDefinition('vite.manifest.registry');
            $definition->setArgument(0, $apps);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new ViteConfiguration();
    }
}
