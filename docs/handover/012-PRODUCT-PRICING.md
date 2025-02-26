# Product Pricing

## Objective

Products must be priced based on their cost. Because the system supports 
dispatching orders in two different ways (seller-fulfilled and Fulfilled 
By Amazon) there are two different price points: 

  - Per Product
  - Per Country

Whenever something impacts the cost of a product, that product must go
through the pricing process again. You'll see a button on the Product 
page and on the Sales Order page called "Send To Pricing", and when
viewing a shipment this will be called "Calculate Landed Costs". 

They both result in the Product Pricing queue being filled up. The queue 
is then worked through by a person. 

## Key Classes

 - `Services\PricingService`
 - `Controllers\Products\ProductPricingExportController`
 - `Api\Products\ProductPricingController`

## Process

### Send to Pricing

The database structure for pricing is that each product has zero or more 
`ProductPricing` objects. _Originally_ this represented the price for a single 
product in EUR and GBP. Both stuck in the same model. Over time, we needed full, 
international pricing so now `ProductPricing` represents a "round" of pricing
and off that hangs multiple `ProductPricingCountry` models - where each one of
those represents the price of that product in a given country. 


During `PricingService::sendToPricing` it will create a new `ProductPricing`
and then for each country, create a new `ProductPricingCountry`.

### Adding a new country
This means that if a country is added, and `has_pricing` is checked, then there 
won't be a way to price up that country until the product goes through 
`sendToPricing`. To be absolutely clear, any country that you want to price
for must have `has_pricing` set to 1. 

### Approval and Export

The UI for product pricing captures: the SF price, the FBA price the various 
fees for each and then calculates the expected margin and total cost for
that item. The user then submits the price and that price then must be 
approved.

All of that takes place so that an export can be generated which works as 
an instruction sheet to someone - they need to log in to Amazon and change
the prices as set out in the export.

### Technical Debt

You'll notice that in `ProductPricingExportController` that the order of
the countries is specified manually. There isn't a DB-driven way of doing this 
so it's done by hand: 

```php
$eu = Country::query()->
  ...
    'de' => '0000',
    'fr' => '0001',
    'it' => '0002',
    'es' => '0003',
    'nl' => '0004',
    'se' => '0005',
    'pl' => '0006',
 ...
```


