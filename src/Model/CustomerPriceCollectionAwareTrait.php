<?php

declare(strict_types=1);

namespace BestIt\CtCustomerPricesBundle\Model;

/**
 * Helps you with the customer price collection.
 *
 * @author blange <lange@bestit-online.de>
 * @package BestIt\CtCustomerPricesBundle\Model
 */
trait CustomerPriceCollectionAwareTrait
{
    /**
     * @var CustomerPriceCollection|null The collection of the customer prices.
     */
    protected $customerPriceCollection;

    /**
     * Returns the collection of the customer prices.
     *
     * We used a getter to help you with strict typing!
     *
     * @return CustomerPriceCollection
     */
    public function getCustomerPriceCollection(): CustomerPriceCollection
    {
        return $this->customerPriceCollection;
    }

    /**
     * Sets the collection of the customer prices.
     *
     * @param CustomerPriceCollection $customerPriceCollection
     * @return $this
     */
    public function setCustomerPriceCollection(CustomerPriceCollection $customerPriceCollection): self
    {
        $this->customerPriceCollection = $customerPriceCollection;

        return $this;
    }
}
