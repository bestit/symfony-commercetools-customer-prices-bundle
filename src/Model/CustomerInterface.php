<?php

declare(strict_types=1);

namespace BestIt\CtCustomerPricesBundle\Model;

/**
 * Enforces the getter for the customer id to access special article prices.
 *
 * @author blange <lange@bestit-online.de>
 * @package BestIt\CtCustomerPricesBundle\Model
 */
interface CustomerInterface
{
    /**
     * Returns the id for the customer.
     *
     * @return string
     */
    public function getCustomerIdForArticlePrices(): string;

    /**
     * Returns the currency for the customer.
     *
     * @return string
     */
    public function getCustomerCurrencyForArticlePrices(): string;
}
