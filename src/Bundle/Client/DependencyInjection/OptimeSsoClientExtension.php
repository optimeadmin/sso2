<?php

namespace Optime\Sso\Bundle\Client\DependencyInjection;

use Optime\Sso\Bundle\Client\Factory\UserFactoryInterface;
use Optime\Sso\Bundle\Client\Security\Local\LocalSsoDataFactoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class OptimeSsoClientExtension extends Extension
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

        $container->setParameter('optime_sso_client.local_ip', $config['local_extra_ip']);
        $container->setParameter('optime_sso_client.inject_iframe_resizer', $config['auto_inject_iframe_resizer']);
        $container->setAlias(UserFactoryInterface::class, $config['user_factory_service']);
        $container->setAlias(LocalSsoDataFactoryInterface::class, $config['local_data_factory_service']);

        $container->setParameter('optime_sso_client.temp_private_key', password_hash(
            __DIR__.$config['user_factory_service'], null
        ));
    }
}