<?php

declare(strict_types=1);

namespace BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;

use BestIt\CtCustomerPricesBundle\Model\CustomerInterface;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;
use Commercetools\Commons\Helper\QueryHelper;
use Commercetools\Core\Client;
use Commercetools\Core\Model\Common\Price;
use Commercetools\Core\Model\CustomObject\CustomObject;
use Commercetools\Core\Request\CustomObjects\CustomObjectQueryRequest;
use Symfony\Component\Cache\Adapter\AdapterInterface;

/**
 * Loads a price list out of the custom objects for the given CustomerInterface-object.
 *
 * @author andre.varelmann <andre.varelmann@bestit-online.de>
 * @package BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection
 */
class CustomerPriceCollectionFactory implements CustomerPriceCollectionFactoryInterface
{
    /**
     * The cache suffix.
     *
     * @var string
     */
    const CACHE_SUFFIX = '-customer-prices';

    /**
     * The default cache time for the price collections.
     *
     * @var int
     */
    const DEFAULT_CACHE_TIME = 3600;
    /**
     * Array for given fields
     *
     * @var string
     */
    private $fields;

    /**
     * The search query
     *
     * @var string
     */
    private $query;

    /**
     * The used cache.
     *
     * @var AdapterInterface
     */
    private $cache;

    /**
     * The used commercetools client.
     *
     * @var Client
     */
    private $client;

    /**
     * The customer object container to fetch.
     *
     * @var string
     */
    private $containerName;

    /**
     * Helper to execute queries.
     *
     * @var QueryHelper
     */
    private $queryHelper;

    /**
     * ByUserFactory constructor.
     *
     * @param AdapterInterface $cache The used cache.
     * @param array $fields
     * @param string $query
     * @param Client $client The used commercetools client.
     * @param string $containerName The customer object container to fetch.
     * @param QueryHelper|null $queryHelper
     */
    public function __construct(
        AdapterInterface $cache,
        array $fields,
        string $query,
        Client $client,
        string $containerName,
        QueryHelper $queryHelper = null
    ) {
        $this->fields = $fields;
        $this->query = $query;
        $this->cache = $cache;
        $this->client = $client;
        $this->containerName = $containerName;
        $this->queryHelper = $queryHelper ?? new QueryHelper();
    }

    /**
     * Loads the price collection of the customer and injects the synthetic service.
     *
     * @param CustomerInterface $customer The used customer.
     *
     * @return CustomerPriceCollection
     */
    public function loadPrices(CustomerInterface $customer): CustomerPriceCollection
    {
        $cacheItem = $this->cache->getItem(
            $customer->getCustomerIdForArticlePrices()
            . $customer->getCustomerCurrencyForArticlePrices()
            . self::CACHE_SUFFIX
        );

        if (!$cacheItem->isHit()) {
            $collection = new CustomerPriceCollection();

            // Replace all *Field vars
            $query = str_replace(
                array_map(function ($key) {
                    return '{' . $key . 'Field}';
                }, array_keys($this->fields)),
                array_values($this->fields),
                $this->query
            );

            // Replace all *Value vars
            $variables = [
                'customer' => $customer->getCustomerIdForArticlePrices(),
                'currency' => $customer->getCustomerCurrencyForArticlePrices()
            ];
            $query = str_replace(
                array_map(function ($key) {
                    return '{' . $key . 'Value}';
                }, array_keys($variables)),
                array_values($variables),
                $query
            );

            // Replace other vars
            $query = str_replace('{container}', $this->containerName, $query);

            $allPrices = $this
                ->queryHelper->getAll(
                    $this->client,
                    (new CustomObjectQueryRequest())->where($query)
                );

            array_map(function (CustomObject $object) use ($collection) {
                $collection->addWithArticleId(
                    Price::fromArray($object->getValue()[$this->fields['prices']]),
                    $object->getValue()[$this->fields['article']]
                );
            }, iterator_to_array($allPrices));

            $this->cache->save($cacheItem->set($collection)->expiresAfter(self::DEFAULT_CACHE_TIME));
        }

        return $cacheItem->get();
    }
}
