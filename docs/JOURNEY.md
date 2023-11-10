# China

WS Raises an order from China. He'll get back an invoice (eventually) which 
is priced in USD. We use the conversion rate, the shipping amount and the
volume of what's been shipped to calculate a projected, UK cost. 
 
This is stored on each product as "est_landed_cost_gbp". 

The UK side will create an invoice in a few weeks time for this CN originated
Sales Order. It'll have the amount we're GOING to pay. We'll enter, 
on each invoice line, the ultimate, final,  per unit GBP price. When 
the invoice is approved, the product's `unit_price_gbp` will be updated.


# UK

"Sales Order" and "Order Acknowledgement" are the same thing but 
raised as a result of UK vs China order. 

When the UK team want to buy from Keel, they will raise an "order 
acknowledgement". It's a sales order basically. It's "we want to by 300 x 
K113" @ £4.50 a unit. 

They will enter in the price that we think we're going to pay on this. It'll
pull from $product->unit_price_gbp if we have it. 

If the user changes that per item cost, we do NOT update 
`$product->unit_price_gbp`.

At this point, we'll then raise an invopice and the same logic applies, we'll
pull in the line price from the OA, but once it's approved, we will copy
that price over to `$product->unit_price_gbp`.

When we send to pricing, we're asking a user to put a retail price on
a product, given an assumed cost of `$product->unit_price_gbp`.