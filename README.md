# bestit/commercetools-customer-prices-bundle 

There is no real support for individual customer prices in the the commercetools platform at this moment. You could try to add a lot of "channel prices" for your products, but you will hit some topics like a missing fallback in the search facets for the [scopedPrice](https://dev.commercetools.com/http-api-projects-products-search.html#filter-by-scoped-price) (no fallback to the normal price, if a channel price is missing), performance losses because of huge product data sets and maybe you hit rock bottom with the maximum document size of the database itself. 

So you need a substitute. This bundle will provide you with a substitute based on custom objects like 

```json
{
    "id": "UUID",
    "version": 1,
    "container": "YOUR-CONTAINER-NAME",
    "key": "KEY-1-2",
    "value": {
        "price": {
            "centAmount": 1234,
            "currencyCode": "EUR"
        },
        "customer": "1",
        "article": "2"
    },
    "createdAt": "2017-08-04T06:51:44.642Z",
    "lastModifiedAt": "2017-08-14T00:04:08.763Z"
}
```

You can configure the field names for the container, price value, article and customer value!

## API

You can inject/use the service **best_it_ct_customer_prices.model.customer_price_collection** to fetch your price with **BestIt\CtCustomerPricesBundle\Model\CustomerPriceCollection::getByArticle(string $articleId)**. $articleId needs 
to match the data out of your custom object.

The lazy loaded service **best_it_ct_customer_prices.model.customer_price_collection** is "created" with a factory, 
which takes the authed User out of the [Security Token Storage](http://symfony
.com/blog/new-in-symfony-2-6-security-component-improvements). 

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require bestit/commercetools-customer-prices-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new BestIt\CtCustomerPricesBundle\BestItCtCustomerPricesBundle(),
        );

        // ...
    }

    // ...
}
```

### Step 3: Configure the bundle

```yaml
# Default configuration for extension with alias: "best_it_ct_customer_prices"
best_it_ct_customer_prices:

    # Please provide the service id for your cache adapter.
    cache_service_id:    cache.app

    # Please provide the service id for your commercetools client.
    client_service_id:    ~ # Required

    # Please provide the name of the custom object container where the prices are saved.
    container:            customer-prices
    fields:

        # Please provide the name of the custom object value field which saves the article id.
        article:              article

        # Please provide the name of the custom object value field which saves the customer id.
        customer:             customer

        # Please provide the name of the custom object value field which saves the money objects for the price.
        prices:               prices
```

### Step 4: Mark your user object as usable.

Please implement the **BestIt\CtCustomerPricesBundle\Model\CustomerInterface** on your user object. The used id needs
 to match the customer data out of your custom object.
 
## ToDos

* More Docs
* Unittesting
