<?php

declare(strict_types=1);

namespace BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;

use BestIt\CtCustomerPricesBundle\Model\CustomerInterface;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;
use Commercetools\Commons\Helper\QueryHelper;
use Commercetools\Core\Client;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Loads a price list out of the custom objects for current user.
 *
 * @author blange <lange@bestit-online.de>
 * @package BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection
 */
class ByUserFactory
{
    /**
     * @var CustomerPriceCollectionFactory
     */
    private $customerPriceCollectionFactory;

    /**
     * Storage to get the authed user.
     *
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * ByUserFactory constructor.
     *
     * @param CustomerPriceCollectionFactory $customerPriceCollectionFactory
     * @param TokenStorageInterface $tokenStorage Storage to get the authed user.
     */
    public function __construct(
        CustomerPriceCollectionFactory $customerPriceCollectionFactory,
        TokenStorageInterface $tokenStorage
    ) {
        $this->customerPriceCollectionFactory = $customerPriceCollectionFactory;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Creates a collection and loads it with the user data if there is a authed user.
     *
     * @return CustomerPriceCollection
     */
    public function createPriceCollection()
    {
        $collection = new CustomerPriceCollection();

        if (($token = $this->tokenStorage->getToken()) && (($user = $token->getUser()) instanceof CustomerInterface)) {
            $collection = $this->customerPriceCollectionFactory->loadPrices($user);
        }

        return $collection;
    }
}
