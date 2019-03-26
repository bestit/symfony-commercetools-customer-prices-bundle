<?php

namespace Tests\BestIt\CtCustomerPricesBundle\CustomerPriceCollection;

use BestIt\CtCustomerPricesBundle\Model\CustomerInterface;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection\ByUserFactory;
use Commercetools\Core\Client;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Test for ByUserFactory.
 *
 * @author Tim Kellner <tim.kellner@bestit-online.de>
 * @package Tests\BestIt\CtCustomerPricesBundle\CustomerPriceCollection
 */
class ByUserFactoryTest extends TestCase
{
    /**
     * The fields
     *
     * @var array
     */
    private $fields = [];

    /**
     * The query
     *
     * @var string
     */
    private $query;

    /**
     * Defines the query and fields.
     *
     * @throws Exception
     *
     * @return void
     */
    protected function setUp()
    {
        $this->query = 'container="{container}-{currencyValue}-{customerValue}"';

        $this->fields = [
            'article' => random_int(1000, 9999),
            'customer' => random_int(1000, 9999),
            'currency' => random_int(1000, 9999),
            'prices' => random_int(1000, 9999)
        ];
    }

    /**
     * Test for createPriceCollection function.
     *
     * @return void
     */
    public function testCreatePriceCollectionWithUser()
    {
        $fixture = new ByUserFactory(
            $cacheMock = $this->createMock(AdapterInterface::class),
            $this->fields,
            $this->query,
            $this->createMock(Client::class),
            $containerName = (string) random_int(1000, 9999),
            null,
            $tokenStorage = $this->createMock(TokenStorageInterface::class)
        );

        $token = $this->createMock(TokenInterface::class);

        $token
            ->method('getUser')
            ->willReturn($user = $this->createMock(CustomerInterface::class));

        $tokenStorage
            ->method('getToken')
            ->willReturn($token);

        $cacheMock
            ->expects(static::once())
            ->method('getItem')
            ->with(($userId = uniqid()) . ($currency = uniqid()) . '-customer-prices')
            ->willReturn($item = $this->createMock(CacheItemInterface::class));

        $item
            ->expects(static::once())
            ->method('isHit')
            ->willReturn(true);

        $item
            ->expects(static::once())
            ->method('get')
            ->willReturn($collection = $this->createMock(CustomerPriceCollection::class));

        $user
            ->expects(static::once())
            ->method('getCustomerCurrencyForArticlePrices')
            ->willReturn($currency);

        $user
            ->expects(static::once())
            ->method('getCustomerIdForArticlePrices')
            ->willReturn($userId);

        self::assertSame($collection, $fixture->createPriceCollection());
    }

    /**
     * Test for createPriceCollection function.
     *
     * @return void
     */
    public function testCreatePriceCollectionWithoutUser()
    {
        $fixture = new ByUserFactory(
            $cacheMock = $this->createMock(AdapterInterface::class),
            $this->fields,
            $this->query,
            $clientMock = $this->createMock(Client::class),
            $containerName = (string) random_int(1000, 9999),
            null,
            $this->createMock(TokenStorageInterface::class)
        );

        self::assertInstanceOf(CustomerPriceCollection::class, $fixture->createPriceCollection());
    }
}
