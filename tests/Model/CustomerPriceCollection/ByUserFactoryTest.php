<?php

namespace Tests\BestIt\CtCustomerPricesBundle\CustomerPriceCollection;

use ArrayObject;
use BestIt\CtCustomerPricesBundle\Model\CustomerInterface;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection\ByUserFactory;
use Commercetools\Commons\Helper\QueryHelper;
use Commercetools\Core\Client;
use Commercetools\Core\Model\CustomObject\CustomObject;
use Commercetools\Core\Request\CustomObjects\CustomObjectQueryRequest;
use Commercetools\Core\Request\Query\MultiParameter;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use ReflectionObject;
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
     * Test for createPriceCollection function.
     */
    public function testCreatePriceCollectionWithoutUser()
    {
        $fixture = new ByUserFactory(
            $cacheMock = $this->createMock(AdapterInterface::class),
            $articleField = (string) random_int(1000, 9999),
            $clientMock = $this->createMock(Client::class),
            $containerName = (string) random_int(1000, 9999),
            $customerField = (string) random_int(1000, 9999),
            $priceField = (string) random_int(1000, 9999),
            $tokenStorageMock = $this->createMock(TokenStorageInterface::class)
        );

        self::assertInstanceOf(CustomerPriceCollection::class, $fixture->createPriceCollection());
    }

    /**
     * Test to load prices from cache function.
     */
    public function testLoadCachedPrices()
    {
        $fixture = new ByUserFactory(
            $cacheMock = $this->createMock(AdapterInterface::class),
            $articleField = (string) random_int(1000, 9999),
            $clientMock = $this->createMock(Client::class),
            $containerName = (string) random_int(1000, 9999),
            $customerField = (string) random_int(1000, 9999),
            $priceField = (string) random_int(1000, 9999),
            $tokenStorageMock = $this->createMock(TokenStorageInterface::class)
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

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock
            ->method('getUser')
            ->willReturn($userMock);

        $tokenStorageMock
            ->method('getToken')
            ->willReturn($tokenMock);


        self::assertSame($priceCollectionMock, $fixture->createPriceCollection());
    }

    /**
     * Test to load prices.
     */
    public function testLoadPrices()
    {
        $fixture = new ByUserFactory(
            $cacheMock = $this->createMock(AdapterInterface::class),
            $articleField = (string) random_int(1000, 9999),
            $clientMock = $this->createMock(Client::class),
            $containerName = (string) random_int(1000, 9999),
            $customerField = (string) random_int(1000, 9999),
            $priceField = (string) random_int(1000, 9999),
            $tokenStorageMock = $this->createMock(TokenStorageInterface::class),
            $queryHelperMock = $this->createMock(QueryHelper::class)
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
            ->with(ByUserFactory::DEFAULT_CACHE_TIME)
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
            ->willReturn($customerId = (string) random_int(1000, 9999));

        $customObject = CustomObject::fromArray([
            'key' => '123',
            'value' => [
                $articleField => '123',
                $priceField => [
                    'value' => [
                        'centAmount' => 1000,
                        'currencyCode' => 'EUR'
                    ]
                ],
            ]
        ]);

        $queryHelperMock
            ->method('getAll')
            ->with(
                $clientMock,
                self::callback(
                    function (CustomObjectQueryRequest $request) use ($containerName, $customerField, $customerId) {
                        $reflectionObject = new ReflectionObject($request);
                        $paramsProperty = $reflectionObject->getProperty('params');
                        $paramsProperty->setAccessible(true);
                        /** @var MultiParameter[] $params */
                        $params = $paramsProperty->getValue($request);

                        self::assertSame(
                            'container="' . $containerName . '"',
                            $params['where=container%3D%22' . $containerName . '%22']->getValue()
                        );

                        self::assertSame(
                            'value(' . $customerField . '="' . $customerId . '")',
                            $params['where=value%28' . $customerField . '%3D%22' . $customerId . '%22%29']->getValue()
                        );

                        return true;
                    }
                )
            )
            ->willReturn(new ArrayObject([$customObject]));

        self::assertSame($priceCollection, $fixture->loadPrices($userMock));
    }
}
