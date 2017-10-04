<?php

namespace Tests\BestIt\CtCustomerPricesBundle\DependencyInjection;

use BestIt\CtCustomerPricesBundle\DependencyInjection\BestItCtCustomerPricesExtension;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection\ByUserFactory;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * Tests for bundle extension.
 *
 * @author Tim Kellner <tim.kellner@bestit-online.de>
 * @package Tests\BestIt\CtCustomerPricesBundle\DependencyInjection
 */
class BestItCtCustomerPricesExtensionTest extends AbstractExtensionTestCase
{
    /**
     * Get extensions.
     *
     * @return ExtensionInterface[]
     */
    public function getContainerExtensions(): array
    {
        return [new BestItCtCustomerPricesExtension()];
    }

    /**
     * Get declared services.
     *
     * @return array
     */
    public function getDeclaredServices(): array
    {
        return [
            ['best_it_ct_customer_prices.model.customer_price_collection', CustomerPriceCollection::class],
            ['best_it_ct_customer_prices.model_customer_price_collection.by_user_factory', ByUserFactory::class]
        ];
    }

    /**
     * Set up the test.
     */
    protected function setUp()
    {
        parent::setUp();

        $config = [
            'container' => (string) random_int(10000, 99999),
            'fields' => [
                'article' => (string) random_int(10000, 99999),
                'customer' => (string) random_int(10000, 99999),
                'prices' => (string) random_int(10000, 99999)
            ],
            'cache_service_id' => (string) random_int(10000, 99999),
            'client_service_id' => (string) random_int(10000, 99999)
        ];

        $this->load($config);
    }

    /**
     * Checks if a declared service exists.
     *
     * @dataProvider getDeclaredServices
     *
     * @param string $serviceId Service id.
     * @param string $serviceClass Service class.
     * @param string $tag Service tag.
     *
     * @return void
     */
    public function testDeclaredServices(string $serviceId, string $serviceClass = '', string $tag = '')
    {
        $this->assertContainerBuilderHasService($serviceId, $serviceClass ?: null);

        if ($tag) {
            $this->assertContainerBuilderHasServiceDefinitionWithTag($serviceId, $tag);
        }
    }

    /**
     * Test that the load function use correct config values.
     *
     * @return void
     */
    public function testLoad()
    {
        $config = [
            [
                'container' => $container = (string) random_int(10000, 99999),
                'fields' => [
                    'article' => $article = (string) random_int(10000, 99999),
                    'customer' => $customer = (string) random_int(10000, 99999),
                    'prices' => $prices = (string) random_int(10000, 99999)
                ],
                'cache_service_id' => $cacheServiceId = (string) random_int(10000, 99999),
                'client_service_id' => $clientServiceId = (string) random_int(10000, 99999)
            ]
        ];

        $containerMock = $this->createMock(ContainerBuilder::class);

        $containerMock
            ->expects(self::at(0))
            ->method('setParameter')
            ->with('best_it_ct_customer_prices.container', $container);

        $containerMock
            ->expects(self::at(1))
            ->method('setParameter')
            ->with('best_it_ct_customer_prices.fields.article', $article);

        $containerMock
            ->expects(self::at(2))
            ->method('setParameter')
            ->with('best_it_ct_customer_prices.fields.customer', $customer);

        $containerMock
            ->expects(self::at(3))
            ->method('setParameter')
            ->with('best_it_ct_customer_prices.fields.prices', $prices);

        $containerMock
            ->expects(self::at(4))
            ->method('setAlias')
            ->with('best_it_ct_customer_prices.cache_adapter', $cacheServiceId);

        $containerMock
            ->expects(self::at(5))
            ->method('setAlias')
            ->with('best_it_ct_customer_prices.commercetools_client', $clientServiceId);

        $fixture = new BestItCtCustomerPricesExtension();
        $fixture->load($config, $containerMock);
    }
}
