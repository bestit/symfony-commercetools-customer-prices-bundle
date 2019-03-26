<?php

namespace Tests\BestIt\CtCustomerPricesBundle\DependencyInjection;

use BestIt\CtCustomerPricesBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * Tests for bundle configuration.
 *
 * @author Tim Kellner <tim.kellner@bestit-online.de>
 * @package Tests\BestIt\CtCustomerPricesBundle\DependencyInjection
 */
class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    /**
     * Get bundle configuration.
     *
     */
    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }

    /**
     * Test the configuration.
     *
     * @return void
     */
    public function testConfiguration()
    {
        $config = [
            [
                'container' => (string) random_int(10000, 99999),
                'fields' => [
                    'article' => (string) random_int(10000, 99999),
                    'customer' => (string) random_int(10000, 99999),
                    'prices' => (string) random_int(10000, 99999)
                ],
                'cache_service_id' => (string) random_int(10000, 99999),
                'client_service_id' => (string) random_int(10000, 99999)
            ]
        ];

        $this->assertConfigurationIsValid($config);
    }
}
