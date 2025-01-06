<?php

namespace Optime\Sso\Bundle\Client\DependencyInjection;

use Optime\Sso\Bundle\Server\Token\User\UserDataFactoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\DirectoryResource;
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

//        $container->setParameter('optime_sso_server.jwt.secret', $config['jwt_secret_key']);
//        $container->setParameter('optime_sso_server.jwt.expiration', $config['jwt_expiration_seconds']);
//        $container->setAlias(UserDataFactoryInterface::class, $config['user_data_factory_service']);

        $container->addResource(new DirectoryResource(__DIR__.'/../'));
    }
}