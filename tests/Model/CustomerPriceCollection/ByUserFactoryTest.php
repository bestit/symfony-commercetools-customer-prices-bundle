<?php

namespace Tests\BestIt\CtCustomerPricesBundle\CustomerPriceCollection;

use BestIt\CtCustomerPricesBundle\Model\CustomerInterface;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection\ByUserFactory;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection\CustomerPriceCollectionFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
     * Test for createPriceCollection function.
     */
    public function testCreatePriceCollectionWithoutUser()
    {
        $fixture = new ByUserFactory(
            $this->createMock(CustomerPriceCollectionFactory::class),
            $this->createMock(TokenStorageInterface::class)
        );

        self::assertInstanceOf(CustomerPriceCollection::class, $fixture->createPriceCollection());
    }

    /**
     * Test for createPriceCollection function.
     */
    public function testCreatePriceCollectionWithUser()
    {
        $fixture = new ByUserFactory(
            $customerPriceCollectionFactory = $this->createMock(CustomerPriceCollectionFactory::class),
            $tokenStorage = $this->createMock(TokenStorageInterface::class)
        );

        $user = $this->createMock(CustomerInterface::class);

        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($user);

        $tokenStorage
            ->method('getToken')
            ->willReturn($token);
        $customerPriceCollectionFactory
            ->method('loadPrices')
            ->with($user)
            ->willReturn(new CustomerPriceCollection());

        self::assertInstanceOf(CustomerPriceCollection::class, $fixture->createPriceCollection());
    }
}
