<?php

namespace Optime\Sso\Bundle\Server\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('optime_sso_server');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('server_code')->isRequired()->end()
                ->scalarNode('user_data_factory_service')->isRequired()->end()
                ->scalarNode('jwt_secret_key')->isRequired()->end()
                ->scalarNode('jwt_expiration_seconds')->defaultValue(10)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}