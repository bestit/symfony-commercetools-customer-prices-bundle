<?php

declare(strict_types=1);

namespace BestIt\CtCustomerPricesBundle\Model;

use Commercetools\Core\Model\Common\Price;
use Commercetools\Core\Model\Common\PriceCollection;

/**
 * Collects special article prices.
 *
 * @author blange <lange@bestit-online.de>
 * @package BestIt\CtCustomerPricesBundle\Model
 */
class CustomerPriceCollection extends PriceCollection
{
    /**
     * The key for the special index sorted by the article id.
     *
     * @var string
     */
    const INDEX_KEY_ARTICLE_NO = 'articleNo';

    /**
     * @var int Counter for the prices in this collection.
     */
    private $count = 0;

    /**
     * Adds a price for the given article id.
     *
     * @param Price $price The found price.
     * @param string $articleId The found article id.
     *
     * @return void
     */
    public function addWithArticleId(Price $price, string $articleId)
    {
        $this->add($price);

        // Offset-Calc like the original setAt
        $this->addToIndex(self::INDEX_KEY_ARTICLE_NO, $this->count++, $articleId);
    }

    /**
     * Returns the count of the prices in this collection.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->count ?: parent::count();
    }

    /**
     * Returns the price by its currency.
     *
     * @param string $articleId The search article id.
     *
     * @return Price|null
     */
    public function getByArticle(string $articleId)
    {
        return $this->getBy(self::INDEX_KEY_ARTICLE_NO, $articleId);
    }
}
