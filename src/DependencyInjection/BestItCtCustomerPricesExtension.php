<?php

namespace BestIt\CtCustomerPricesBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Loads the config for the app bundle.
 * @author lange <lange@bestit-online.de>
 * @package BestIt\CtCustomerPricesBundle\DependencyInjection
 */
class BestItCtCustomerPricesExtension extends Extension
{
    /**
     * Loads the bundle config.
     * @param array $configs
     * @param ContainerBuilder $container
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter('best_it_ct_customer_prices.container', $config['container']);
        $container->setParameter('best_it_ct_customer_prices.fields.article', $config['fields']['article']);
        $container->setParameter('best_it_ct_customer_prices.fields.customer', $config['fields']['customer']);
        $container->setParameter('best_it_ct_customer_prices.fields.currency', $config['fields']['currency']);
        $container->setParameter('best_it_ct_customer_prices.fields.prices', $config['fields']['prices']);

        $container->setAlias('best_it_ct_customer_prices.cache_adapter', $config['cache_service_id']);
        $container->setAlias('best_it_ct_customer_prices.commercetools_client', $config['client_service_id']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}
