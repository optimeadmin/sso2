<?php

namespace Optime\Sso\Bundle\Server\DependencyInjection;

use Optime\Sso\Bundle\Server\Token\User\UserDataFactoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class OptimeSsoServerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../_config')
        );
        $loader->load('services.yaml');

//        $container->setParameter('optime_acl.enabled', $config['enabled']);
//        $container->setParameter('optime_acl.cache_voters', $config['cache_voters']);
//        $container->setParameter('optime_acl.header.title', $config['header']['title']);
//        $container->setParameter('optime_acl.header.path', $config['header']['path']);
//
//        $this->configureRolesProvider($config, $container);
//        $this->configureResourcesPrefixes($config, $container);

        $container->addResource(new DirectoryResource(__DIR__.'/../'));

        $container->setAlias(UserDataFactoryInterface::class, $config['user_data_factory_service']);
//        $container->setAlias($config['user_data_factory_service'], UserDataFactoryInterface::class);
    }
}