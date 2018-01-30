<?php

declare(strict_types=1);

namespace BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;

use BestIt\CtCustomerPricesBundle\Model\CustomerInterface;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;
use Commercetools\Core\Client;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Loads a price list out of the custom objects for current user.
 *
 * @author blange <lange@bestit-online.de>
 * @package BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection
 */
class ByUserFactory extends CustomerPriceCollectionFactory
{
    /**
     * Storage to get the authed user.
     *
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * ByUserFactory constructor.
     *
     * @param AdapterInterface $cache The used cache.
     * @param array $fields
     * @param string $query
     * @param Client $client The used commercetools client.
     * @param string $containerName The customer object container to fetch.
     * @param Stopwatch $stopwatch
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        AdapterInterface $cache,
        array $fields,
        string $query,
        Client $client,
        string $containerName,
        Stopwatch $stopwatch = null,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($cache, $fields, $query, $client, $containerName, $stopwatch);

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
            $collection = parent::loadPrices($user);
        }

        return $collection;
    }
}
