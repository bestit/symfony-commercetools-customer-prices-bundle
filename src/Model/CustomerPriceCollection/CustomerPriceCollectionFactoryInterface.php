<?php

declare(strict_types=1);

namespace BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;

use BestIt\CtCustomerPricesBundle\Model\CustomerInterface;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;

/**
 * Loads a price list out of the custom objects for the given CustomerInterface-object.
 *
 * @author andre.varelmann <andre.varelmann@bestit-online.de>
 * @package BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection
 */
interface CustomerPriceCollectionFactoryInterface
{
    /**
     * Loads the price collection of the customer and injects the synthetic service.
     *
     * @param CustomerInterface $customer The used customer.
     *
     * @return CustomerPriceCollection
     */
    public function loadPrices(CustomerInterface $customer): CustomerPriceCollection;
}
