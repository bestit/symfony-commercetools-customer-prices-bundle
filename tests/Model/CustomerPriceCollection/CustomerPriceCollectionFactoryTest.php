<?php

namespace Tests\BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;

use BestIt\CtCustomerPricesBundle\Model\CustomerInterface;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection\CustomerPriceCollectionFactory;
use Commercetools\Commons\Helper\QueryHelper;
use Commercetools\Core\Client;
use Commercetools\Core\Model\CustomObject\CustomObject;
use Commercetools\Core\Request\CustomObjects\CustomObjectQueryRequest;
use Commercetools\Core\Response\PagedQueryResponse;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;

/**
 * Test for CustomerPriceCollectionFactory
 *
 * @author andre.varelmann <andre.varelmann@bestit-online.de>
 * @package Tests\BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection
 */
class CustomerPriceCollectionFactoryTest extends TestCase
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
     * {@inheritdoc}
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
     * Checks the default return of the getter.
     *
     * @return void
     */
    public function testGetBatchSizeDefault()
    {
        $fixture = new CustomerPriceCollectionFactory(
            $this->createMock(AdapterInterface::class),
            $this->fields,
            $this->query,
            $this->createMock(Client::class),
            (string)random_int(1000, 9999)
        );

        static::assertSame(QueryHelper::DEFAULT_PAGE_SIZE, $fixture->getBatchSize());
    }

    /**
     * Test to load prices from cache function.
     */
    public function testLoadCachedPrices()
    {
        $fixture = new CustomerPriceCollectionFactory(
            $cacheMock = $this->createMock(AdapterInterface::class),
            $this->fields,
            $this->query,
            $clientMock = $this->createMock(Client::class),
            $containerName = (string)random_int(1000, 9999)
        );

        $priceCollectionMock = $this->createMock(CustomerPriceCollection::class);

        $cacheItemMock = $this->createMock(CacheItemInterface::class);
        $cacheItemMock
            ->method('isHit')
            ->willReturn(true);
        $cacheItemMock
            ->method('get')
            ->willReturn($priceCollectionMock);

        $cacheMock
            ->method('getItem')
            ->willReturn($cacheItemMock);

        $userMock = $this->createMock(CustomerInterface::class);
        $userMock
            ->method('getCustomerIdForArticlePrices')
            ->willReturn($customerId = (string)random_int(1000, 9999));
        $userMock
            ->method('getCustomerCurrencyForArticlePrices')
            ->willReturn($currency = (string)random_int(1000, 9999));

        self::assertSame($priceCollectionMock, $fixture->loadPrices($userMock));
    }

    /**
     * Test to load prices.
     *
     * @return void
     */
    public function testLoadPrices()
    {
        $fixture = new CustomerPriceCollectionFactory(
            $cacheMock = $this->createMock(AdapterInterface::class),
            $this->fields,
            $this->query,
            $clientMock = $this->createMock(Client::class),
            $containerName = (string)random_int(1000, 9999)
        );

        $cacheItemMock = $this->createMock(CacheItemInterface::class);
        $cacheItemMock
            ->method('isHit')
            ->willReturn(false);

        $cacheItemMock
            ->method('set')
            ->with(self::callback(
                function (CustomerPriceCollection $collection) {
                    $price = $collection->getByArticle('123');

                    self::assertSame('EUR', $price->getValue()->getCurrencyCode());
                    self::assertSame(1000, $price->getValue()->getCentAmount());

                    return true;
                }
            ))
            ->willReturn($cacheItemMock);

        $cacheItemMock
            ->method('expiresAfter')
            ->with(CustomerPriceCollectionFactory::DEFAULT_CACHE_TIME)
            ->willReturn($cacheItemMock);

        $cacheItemMock
            ->method('get')
            ->willReturn($priceCollection = $this->createMock(CustomerPriceCollection::class));

        $cacheMock
            ->method('getItem')
            ->willReturn($cacheItemMock);

        $userMock = $this->createMock(CustomerInterface::class);

        $userMock
            ->method('getCustomerIdForArticlePrices')
            ->willReturn($customerId = (string)random_int(1000, 9999));

        $userMock
            ->method('getCustomerCurrencyForArticlePrices')
            ->willReturn($currency = (string)random_int(1000, 9999));

        $customObject = CustomObject::fromArray([
            'id' => $firstObjectId = uniqid(),
            'key' => '123',
            'value' => [
                $this->fields['article'] => '123',
                $this->fields['prices'] => [
                    'value' => [
                        'centAmount' => 1000,
                        'currencyCode' => 'EUR'
                    ]
                ],
            ]
        ]);

        $clientMock
            ->expects(static::at(0))
            ->method('execute')
            ->with(self::callback(function ($request) use ($containerName, $customerId, $currency) {
                /** @var CustomObjectQueryRequest $request */
                static::assertInstanceOf(CustomObjectQueryRequest::class, $request, 'Wrong instance. (1)');
                static::assertSame(
                    http_build_query([
                        'limit' => 1,
                        'sort' => 'id',
                        'where' => sprintf('container="%s-%s-%s"', $containerName, $currency, $customerId),
                        'withTotal' => 'false'
                    ]),
                    $request->httpRequest()->getUri()->getQuery(),
                    'Wrong Query.'
                );

                return true;
            }))
            ->willReturn($responseMock1 = $this->createMock(PagedQueryResponse::class));

        $responseMock1
            ->expects(static::once())
            ->method('toArray')
            ->willReturn(['results' => [$customObject->toArray()]]);

        $clientMock
            ->expects(static::at(1))
            ->method('execute')
            ->with(self::callback(function ($request) use ($containerName, $customerId, $currency, $firstObjectId) {
                /** @var CustomObjectQueryRequest $request */
                static::assertInstanceOf(CustomObjectQueryRequest::class, $request, 'Wrong instance. (1)');
                static::assertSame(
                    sprintf(
                        'limit=%d&sort=id&where=container%%3D%%22%d-%d-%d%%22&where=id+%%3E+%%22%s%%22&withTotal=false',
                        1,
                        $containerName,
                        $currency,
                        $customerId,
                        $firstObjectId
                    ),
                    $request->httpRequest()->getUri()->getQuery(),
                    'Wrong Query.'
                );

                return true;
            }))
            ->willReturn($responseMock2 = $this->createMock(PagedQueryResponse::class));

        $responseMock2
            ->expects(static::once())
            ->method('toArray')
            ->willReturn(['results' => []]);

        self::assertSame($priceCollection, $fixture->setBatchSize(1)->loadPrices($userMock));
    }
}
