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

        return $treeBuilder;
    }
}