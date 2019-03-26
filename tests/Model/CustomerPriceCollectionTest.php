<?php

namespace Tests\BestIt\CtCustomerPricesBundle\Model;

use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;
use Commercetools\Core\Model\Common\Price;
use PHPUnit\Framework\TestCase;

/**
 * Test for price collection.
 *
 * @author Tim Kellner <tim.kellner@bestit-online.de>
 * @package Tests\BestIt\CtCustomerPricesBundle\Model
 */
class CustomerPriceCollectionTest extends TestCase
{
    /**
     * Test addWithArticleId function.
     *
     * @return void
     */
    public function testAddAndGetWithArticleId()
    {
        $price = $this->createMock(Price::class);
        $articleId = (string) random_int(1000, 9999);

        $fixture = new CustomerPriceCollection();

        $fixture->addWithArticleId($price, $articleId);

        self::assertSame($price, $fixture->getByArticle($articleId));
    }
}
