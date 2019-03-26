<?php

declare(strict_types=1);

namespace BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;

use BestIt\CtCustomerPricesBundle\Model\CustomerInterface;
use BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection;
use Commercetools\Commons\Helper\QueryHelper;
use Commercetools\Core\Client;
use Commercetools\Core\Model\Common\Price;
use Commercetools\Core\Request\CustomObjects\CustomObjectQueryRequest;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Stopwatch\Stopwatch;

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
     * @var int T
     */
    private $batchSize;

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
     * Helper to execute queries.
     *
     * @var QueryHelper
     */
    private $queryHelper;

    /**
     * @var Stopwatch The used stop watch.
     */
    private $stopwatch;

    /**
     * ByUserFactory constructor.
     *
     * @param AdapterInterface $cache The used cache.
     * @param array $fields
     * @param string $query
     * @param Client $client The used commercetools client.
     * @param string $containerName The customer object container to fetch.
     * @param Stopwatch $stopwatch
     */
    public function __construct(
        AdapterInterface $cache,
        array $fields,
        string $query,
        Client $client,
        string $containerName,
        Stopwatch $stopwatch = null
    ) {
        $this->fields = $fields;
        $this->query = $query;
        $this->cache = $cache;
        $this->client = $client;
        $this->containerName = $containerName;
        $this->queryHelper = $queryHelper ?? new QueryHelper();
        $this->stopwatch = $this->stopwatch ?? new Stopwatch();

        $this->setBatchSize(QueryHelper::DEFAULT_PAGE_SIZE);
    }

    /**
     * Creates the query for the given customer.
     *
     * @param CustomerInterface $customer
     *
     * @return string
     */
    private function createQuery(CustomerInterface $customer): string
    {
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
        return str_replace('{container}', $this->containerName, $query);
    }

    /**
     * Fills the price collection by executing the query.
     *
     * @param string $query
     *
     * @return CustomerPriceCollection
     */
    private function fillCollectionByQuery(string $query): CustomerPriceCollection
    {
        $collection = new CustomerPriceCollection();
        $request = (new CustomObjectQueryRequest())->where($query);

        $batchCount = 1;
        $batchSize = $this->getBatchSize();
        $lastId = '';

        $this->stopwatch->openSection();

        $request->sort('id')->limit($batchSize)->withTotal(false);

        do {
            $this->stopwatch->start('load-batch.' . ($thisBatch = $batchCount++));

            if ($lastId) {
                $request->where('id > "' . $lastId . '"');
            }

            $response = $this->client->execute($request);

            if ($response->isError() || (!$results = $response->toArray()['results'])) {
                break;
            }

            foreach ($results as $customObject) {
                $lastId = $customObject['id'];

                $collection->addWithArticleId(
                    Price::fromArray($customObject['value'][$this->fields['prices']]),
                    $customObject['value'][$this->fields['article']]
                );
            }

            $this->stopwatch->stop('load-batch.' . $thisBatch);
        } while ($results && count($results) >= $batchSize);

        $this->stopwatch->stopSection(__METHOD__);

        return $collection;
    }

    /**
     * Writes the filled collection into the cache.
     *
     * @param CustomerInterface $customer
     * @param CacheItemInterface $cacheItem
     *
     * @return void
     */
    private function fillCollectionCache(CustomerInterface $customer, CacheItemInterface $cacheItem)
    {
        $query = $this->createQuery($customer);

        $collection = $this->fillCollectionByQuery($query);

        $this->cache->save($cacheItem->set($collection)->expiresAfter(self::DEFAULT_CACHE_TIME));
    }

    /**
     * Returns the used batch size for custom object requests.
     *
     * @return int
     */
    public function getBatchSize(): int
    {
        return $this->batchSize;
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
            $this->fillCollectionCache($customer, $cacheItem);
        }

        return $cacheItem->get();
    }

    /**
     * Sets the used batch size for custom object requests.
     *
     * @param int $batchSize
     *
     * @return $this
     */
    public function setBatchSize(int $batchSize): CustomerPriceCollectionFactoryInterface
    {
        $this->batchSize = $batchSize;

        return $this;
    }
}
