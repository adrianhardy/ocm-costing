## Dual Exchange Rate
### Added 18 July 2024

### Overview
There are two key places where the exchange rate needs to change.

 - Daily Pricing Viability Check
 - Pricing Cost / Margin Calculation

Currency table currently has `default_fx_to_gbp`. We've added 
`pricing_fx_to_gbp`. Similarly, wherever there's an attribute on Currency 
which manipulates `default_fx_to_gbp` we've made an equivalent for 
`pricing_fx_to_gbp`. 

The biggest change made is that we'll now use `current_fx_to_gbp` which is a 
"fake" attribute. When we want to get the "pricing if we have one, if not then 
the default" currency rate, we'll call `current_fx_to_gbp`.

So now, DailyPricing's `updateViability` uses `current_fx_to_gbp` to check if
a price is viable.

Similarly, when we price something, we'll use `current_fx_to_gbp` to work out
the margin after costs. 

### Testing

Very ignorantly, we ran a product through daily pricing. It was viable, we
change the 2nd exchange rate to be _worse_ than the default exchange rate, 
and suddenly the same price with the same FBA fee is no longer viable.