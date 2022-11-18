<?php

declare(strict_types=1);

namespace MakinaCorpus\ViteBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

final class ViteConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('vite');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('app')
                    ->normalizeKeys(true)
                    ->prototype('array')
                        ->children()
                            ->scalarNode('manifest')
                                ->info("manifest.json file absolute path or 'public/' directory relative path.")
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
