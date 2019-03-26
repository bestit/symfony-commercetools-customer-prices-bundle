<?php

declare(strict_types=1);

namespace BestIt\CtCustomerPricesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configures the bundle.
 *
 * @author blange <lange@bestit-online.de>
 * @package BestIt\CtCustomerPricesBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder();

        $builder->root('best_it_ct_customer_prices')
            ->children()
                ->scalarNode('cache_service_id')
                    ->defaultValue('cache.app')
                    ->info('Please provide the service id for your cache adapter.')
                ->end()
                ->scalarNode('client_service_id')
                    ->info('Please provide the service id for your commercetools client.')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('query')
                    ->defaultValue('container="{container}-{currencyValue}-{customerValue}"')
                    ->info('Please provide the search query. You can use placeholder in your query')
                ->end()
                ->scalarNode('container')
                    ->defaultValue('customer-prices')
                    ->info('Please provide the name of the custom object container where the prices are saved.')
                ->end()
                ->arrayNode('fields')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('article')
                            ->defaultValue('article')
                            ->info(
                                'Please provide the name of the custom object value field which saves the ' .
                                'article id.'
                            )
                        ->end()
                        ->scalarNode('customer')
                            ->defaultValue('customer')
                            ->info(
                                'Please provide the name of the custom object value field which saves the ' .
                                'customer id.'
                            )
                        ->end()
                        ->scalarNode('currency')
                            ->defaultValue('currency')
                            ->info(
                                'Please provide the name of the custom object value field which saves the ' .
                                'currency.'
                            )
                        ->end()
                        ->scalarNode('prices')
                            ->defaultValue('prices')
                            ->info(
                                'Please provide the name of the custom object value field which saves the ' .
                                'money objects for the price.'
                            )
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
