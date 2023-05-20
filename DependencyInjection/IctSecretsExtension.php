<?php

namespace Ict\Secrets\DependencyInjection;

use Ict\Secrets\Encoder\EncoderInterface;
use Ict\Secrets\Encoder\SodiumKeysEncoder;
use Ict\Secrets\Vault\RedisVaultStorage;
use Ict\Secrets\Vault\VaultStorageInterface;
use Predis\Client;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class IctSecretsExtension extends Extension
{

    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('services.xml');

        $configuration = new IctSecretsConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->registerForAutoconfiguration(EncoderInterface::class)->addTag('ict.secrets.encoder');
        $container->registerForAutoconfiguration(VaultStorageInterface::class)->addTag('ict.secrets.vault');

        if($config['encoder'] == 'sodium' ) {
            $this->loadSodiumEncoder($container);
        }

        if($config['store']['type'] == 'redis') {
            $this->loadRedisVault($container, $config['store']);
        }
    }

    private function loadSodiumEncoder(ContainerBuilder $container): void
    {
        if(!extension_loaded('sodium')) {
            throw new InvalidConfigurationException('Using sodium as encoder requires installing extension "ext-sodium"');
        }

        $container->register('ict.secrets.keys_encoder', SodiumKeysEncoder::class);
        $container->setAlias(EncoderInterface::class, 'ict.secrets.keys_encoder');
    }

    private function loadRedisVault(ContainerBuilder $container, array $storeConfig): void
    {
        $redisConfig = $storeConfig['config'];
        if(!isset($redisConfig['uri'])) {
            throw new InvalidConfigurationException('Redis configuration requires parameter uri into config key');
        }

        if(!class_exists('Predis\Client')) {
            throw new InvalidConfigurationException('Type redis require installing predis. Use "composer require snc/redis-bundle" to achieve it ');
        }

        $container->setDefinition('ict_secrets.rds_client', new Definition(Client::class))->addArgument($redisConfig['uri']);
        $container
            ->register(RedisVaultStorage::class, RedisVaultStorage::class)
            ->addArgument(new Reference('ict_secrets.rds_client'))
            ->addArgument(new Reference(EncoderInterface::class))
        ;

        $container->setAlias(VaultStorageInterface::class, RedisVaultStorage::class);
    }
}
