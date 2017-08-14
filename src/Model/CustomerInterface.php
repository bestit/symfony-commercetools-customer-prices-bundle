<?php

declare(strict_types=1);

namespace BestIt\CtCustomerPricesBundle\Model;

/**
 * Enforces the getter for the customer id to access special article prices.
 * @author blange <lange@bestit-online.de>
 * @package BestIt\CtCustomerPricesBundle
 */
interface CustomerInterface
{
    /**
     * Returns the id for the customer.
     * @return string
     */
    public function getCustomerIdForArticlePrices(): string;
}
