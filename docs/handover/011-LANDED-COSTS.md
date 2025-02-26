# Landed Cost

## Objective

There are several ways to describe the cost of a unit and they each have a 
different time when they're most appropriate.

The system grew very organically for the first two or three years. As the system
was being used, encountering previously unseen scenarios, we would add a field
to track something, only to find out it may arrive in a not-ideal order. So 
there's lots of "if we don't have this, then use that" logic. 

The solution to a lot of this confusion came with commit `482f1d95`, dated 8th 
November 2023. The message for the commit is _Tweaks while on the phone with 
WS/CC_. 

We went through the system over hours and swapped out whatever figures looked
incorrect in the clients' eyes.  To be clear, "swapped out" rather than "fixed"
because all the data was available, it was just not clear when it should be 
used. The downside of such a phone call is you're left with accessors such as
`final_cost_gbp` which simply proxies to another field with no further logic 
or processing.

## Key Classes
 - `Controllers\Api\Shipments\CalculateLandedCostController`
 - `Domain\LandedCostCalculator`
 - `Domain\ProductPricingViabilityChecker`
 - `Models\ProductLandedCost`

## Terms

### Estimated Landed Cost

This is a "live-ish" view of how much it would cost to ship this product now. 
We calculate estimated landed cost using the `LandedCostCalculator` which uses
a mixture of real figures (e.g. the exchange rate) with approximations (e.g 
roughly, the current cost to ship a cubic foot) to provide and estimated, 
landed cost per unit. 

If a user alters either the unit price or the carton quantity of a product, then
the estimated landed cost is updated. 

### Calculated Landed Cost

When a shipment is received (and the paperwork has been uploaded) we have an
opportunity to work out how much each product cost, based on the shipping and 
factory invoices - probably the most accurate measure one could have.

The challenge sets in that a product may not land in a single shipment - they 
may arrive over multiple shipments. Similarly, multiple orders may arrive in
a single shipment. 

`CalculateLandedCostController` uses `LandedCostCalculator` to work out a more
realistic cost because, as part of the way updating shipments works, we _upload_ 
the authoriative answer to the question "how much did it cost to ship x of
this product?".

### Invoiced Cost

Because UK goods don't arrive on a shipment, the system support the idea of 
raising an "Order Acknowledgement" (which is essentially a paperwork catch up
on the fact that a product has been ordered outside of the system) and that 
will contain the "How much did we pay to get this unit" value. So, raising an 
invoice for a Product/Item will also authoritatively update the landed cost. 

In all three scenarios, for a `Product` model, `unit_price_gbp` represents 
the summarised landed cost.


### Product Pricing vs Daily Pricing

#### Product Pricing

Product Pricing is the process of assigning a retail price to the product,
based on its landed cost. Given a Product, the user will end up creating : 

 - `ProductPricing` (describes a "batch" of pricing for a specific product)
 - `ProductPricingCountry` (what is this product's price, in this country)
 
This information can then be expoirted

#### Daily Pricing

Daily Pricing is the process of remaining actively competative by asking 
"can we match _this_ price?". Users will submit prices that they've seen in a 
competitors store and this price will be evaluated for viability. If it's 
viable, it'll be accepted as a suggestion and then signed off. When it gets 
signed off, someone manually changes the price on Amazon. 

Obviously, we can only do DailyPricing on Products which have a landed cost. 
The launch of this feature ("Daily Pricing") led to the realisation that we
needed a way of manually creating and importing landed costs for products, so 
that the viability test could work on their entire, current product set.

## Confusing Field Names


For `Product` models, the property `unit_price_gbp` refers to the total gross 
cost (in GBP) of getting that item. For other areas of the system (e.g. the 
`product_pricing` table), `unit_price_gbp` refers to the retail price of the
item.

When a `Product` is being updated, someone can assert ther cost of acquiring it
by entering in a `unit_price_gbp`. In the absence of that, the system will look
to the `product_landed_costs` table. A quick way of interrogating that would be
to call `$product->current_landed_cost_gbp` which will fetch the most recent
entry from `produt_landed_costs` (returning a `ProductLandedCost` model).

Just so it's in plain site, here's a full list of fields which sound like
they're describing the same thing:

 - `products.unit_price`
 - `products.unit_price_gbp`
 - `products.est_landed_cost_gbp`
 - `product_landed_costs.unit_price`
 - `product_landed_costs.landed_cost_per_unit`
 - `product_landed_costs.landed_cost_per_unit_gbp`
 
These are the costs to acquire the product, not to be confused with the retail
prices such as:

 - `product_pricing.unit_price_gbp`
 - `product_pricing.gross_unit_cost_gbp`
 - `product_pricing.est_landed_cost_per_unit_gbp`
 - `daily_pricing.suggested_price`

And then you've got the properties / accessors on models such as: 

 - `DailyPricing::last_est_landed_cost`
 - `ProductPricing::calculated_gross_profit_gbp` // unused 
 - `Product::final_cost_gbp` // (now proxies to unit_price_gbp)  
 





