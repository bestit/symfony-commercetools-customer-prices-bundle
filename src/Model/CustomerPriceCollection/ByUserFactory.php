<?php

declare(strict_types=1);

namespace BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;

use BestIt\CtCustomerPricesBundle\Model\CustomerInterface;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;
use Commercetools\Commons\Helper\QueryHelper;
use Commercetools\Core\Client;
use Commercetools\Core\Model\Common\Money;
use Commercetools\Core\Model\Common\Price;
use Commercetools\Core\Model\CustomObject\CustomObject;
use Commercetools\Core\Request\CustomObjects\CustomObjectQueryRequest;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Loads a price list out of the custom objects for the given customer.
 * @author blange <lange@bestit-online.de>
 * @package BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection
 */
class ByUserFactory
{
    /**
     * The cache suffix.
     * @var string
     */
    const CACHE_SUFFIX = '-customer-prices';

    /**
     * The default cache time for the price collections.
     * @var int
     */
    const DEFAULT_CACHE_TIME = 3600;

    /**
     * Where to find the article id.
     * @var string
     */
    private $articleField;

    /**
     * The used cache.
     * @var AdapterInterface
     */
    private $cache;

    /**
     * The used commercetools client.
     * @var Client
     */
    private $client;

    /**
     * The customer object container to fetch.
     * @var string
     */
    private $containerName;

    /**
     * The customer field in the custom objects.
     * @var string
     */
    private $customerField;

    /**
     * The name of the field where the prices can be found.
     * @var string
     */
    private $pricesField;

    /**
     * Storage to get the authed user.
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * ByUserFactory constructor.
     * @param AdapterInterface $cache The used cache.
     * @param string $articleField In which field can the article id be found?
     * @param Client $client The used commercetools client.
     * @param string $containerName The customer object container to fetch.
     * @param string $customerField The customer field in the custom objects.
     * @param string $pricesField The name of the field where the prices can be found.
     * @param TokenStorageInterface $tokenStorage Storage to get the authed user.
     */
    public function __construct(
        AdapterInterface $cache,
        string $articleField,
        Client $client,
        string $containerName,
        string $customerField,
        string $pricesField,
        TokenStorageInterface $tokenStorage
    ) {
        $this->articleField = $articleField;
        $this->cache = $cache;
        $this->client = $client;
        $this->containerName = $containerName;
        $this->customerField = $customerField;
        $this->pricesField = $pricesField;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Creates a collection and loads it with the user data if there is a authed user.
     * @return CustomerPriceCollection
     */
    public function createPriceCollection()
    {
        $collection = new CustomerPriceCollection();

        if (($token = $this->tokenStorage->getToken()) && (($user = $token->getUser()) instanceof CustomerInterface)) {
            $collection = $this->loadPrices($user);
        }

        return $collection;
    }

    /**
     * Loads the price collection of the customer and injects the synthetic service.
     * @param CustomerInterface $customer The used customer.
     * @return CustomerPriceCollection
     * @todo Enable more currencies.
     */
    public function loadPrices(CustomerInterface $customer): CustomerPriceCollection
    {
        $cacheItem = $this->cache->getItem($cacheKey = $customer->getCustomerIdForArticlePrices() . self::CACHE_SUFFIX);

        if (!$cacheItem->isHit()) {
            $collection = new CustomerPriceCollection();

            $allPrices = (new QueryHelper())->getAll(
                $this->client,
                (new CustomObjectQueryRequest())
                    ->where(sprintf('container="%s"', $this->containerName))
                    ->where(
                        sprintf('value(%s="%s")', $this->customerField, $customer->getCustomerIdForArticlePrices())
                    )
            );

            array_map(function (CustomObject $object) use ($collection) {
                $collection->addWithArticleId(
                    Price::ofMoney(Money::fromArray($object->getValue()[$this->pricesField])),
                    $object->getValue()[$this->articleField]
                );
            }, iterator_to_array($allPrices));

            $this->cache->save($cacheItem->set($collection)->expiresAfter(self::DEFAULT_CACHE_TIME));
        }

        return $cacheItem->get();
    }
}
