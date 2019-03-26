<?php

namespace Tests\BestIt\CtCustomerPricesBundle\Model;

use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollectionAwareTrait;
use PHPUnit\Framework\TestCase;

/**
 * Test for price collection aware trait.
 *
 * @author Tim Kellner <tim.kellner@bestit-online.de>
 * @package Tests\BestIt\CtCustomerPricesBundle\Model
 */
class CustomerPriceCollectionAwareTraitTest extends TestCase
{
    /**
     * Test getter and setter.
     *
     * @return void
     */
    public function testTrait()
    {
        $priceCollectionMock = $this->createMock(CustomerPriceCollection::class);

        $fixture = $this->getMockForTrait(CustomerPriceCollectionAwareTrait::class);
        $fixture->setCustomerPriceCollection($priceCollectionMock);
        self::assertSame($priceCollectionMock, $fixture->getCustomerPriceCollection());
    }
}
