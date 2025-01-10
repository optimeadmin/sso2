<?php

namespace Optime\Sso\Bundle\Client\DependencyInjection;

use Optime\Sso\Bundle\Client\Factory\UserFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('optime_sso_client');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('user_factory_service')
                    ->cannotBeEmpty()
                    ->defaultValue(UserFactory::class)
                ->end()
                ->booleanNode('auto_inject_iframe_resizer')->defaultTrue()->end()
//                ->scalarNode('jwt_expiration_seconds')->defaultValue(10)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}