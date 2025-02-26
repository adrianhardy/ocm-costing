# Overview

This system provides seven main functions to the administrative side of 
Orchard Crown's operations. If the warehouse is focused on the middle 
part of the journey, this system focusses on the beginning and end. 

There are two internal audiences for this application. Anyone UK based
isn't interested in seeing some of the CN functionality. Anyone CN
based isn't interested in seeing the UK functionality. To try and create
a separation, there are two entry points:

 - https://ukcosting.orchardcrownmall.co.uk
 - https://cncosting.orchardcrownmall.co.uk

A super user will be able to log in to both.

## Products

Can be thought of as identical to RAF's "Items". They track very similar 
information but a very confusing change had to be made recently: 

We discovered that "item number" and "article number" were in the wrong 
columns. This was after several thousand products over hundreds of imports
had already taken place, so the safest change was to simply relabel the
front end. 

"Item Number" matches RAF's idea of an Item Number and "Article Number" is
a manufacturers reference. So, given the above.... The Item Number is stored
in `article_number` and the Article Number is stored in `item_number`. 

There is an integration between RAF and the Costing system which meant that 
they needed to be talking about the correct, common "Item Number" data, and
this led to the change being made. 

## Orders

After years of experience and relationships with manufacturers in China, 
Orchard Crown have a very prescribed way of ordering items for production from
their manufacturing partners. 

In short, it's an Excel sheet which covers the name, appearance, packaging 
instructions and the agreed unit price (amongst other things). The system 
supports the creation of _Sales Orders_ using that Excel sheet as a starting 
point.

Orders go through several stages (modelled in the system), but the most 
significant stage is when the order has been accepted, approved - "let's go 
ahead". 

## Shipping

Each Order, if approved, will end up on one or more shipments (or consignments
 - a term used to avoid confusion with RAF's use of "shipment"). A single 
consignment may contain one or more orders, but an order may end up spanning
multiple consignments. 

There's a flurry of new terminology with international shipping, but in brief:

 - Bill of Lading - A top-level agreement (sending this to you, and you own it)
 - Vessel Reference - What's the ship called?
 - Packing List - When dealing with thousands of items and containers, there's 
   usually some slight variations in quantities vs what was requested. 
 - Invoice - the reply to the Sales Order
 - MID - Manufacturer ID (international identifier which identifies the sender)
 - Shipping Agent - There's a lot of paperwork involved in the receipt of 
   international goods - these guys sort it.  

The system tracks these because you need as much information to identify the
consignment (the thing we're interested in) because it's described across four
(or so) different documents. 

## Costing

When ordering several thousand items from China, it's important that you 
understand the final, _landed cost_ of that item. There's the unit cost that
you might pay to the factory, but after shipping and duties, the cost of that
item will increase.

This system factors in exchange rates, duties, handling fees as well as 
shipping costs to calculate a final, landed cost for each item. It does that by
allowing a user to upload / input the shipping invoice. The shipping invoice
identifies how much each line (within that consignment) costs to ship. So,
if we know the cost to ship each individual item, we know the cost to order it,
we know what the exchange rate was at the point we ordered it, we can add on 
duties and other operational costs and we end up with a very accurate landed 
cost in GBP.  

## Invoicing

The system started out to service international shipments, but it's expanded to
cover domestic orders as well. 

### China

There's the _Sales Order_, which quotes a unit cost, there's the _Invoice_ 
which received from the manufacturer, and then a further document is raised on
this side which says "this is what we ended up paying per unit".

### UK Supplier

UK Items' costs get tracked similarly, but using the notion of an "Order 
Acknowledgement". Behind the scenes, "Order Acknowledgements" are _identical_
to sales orders because they use the same model/structure as sales orders.

An OA is a much more straight forward document though - there's no complex
shipment calculations, no duties to work out. It's a list of items that we
want to order from a Supplier, along with a unit cost. 

## Pricing

We've spent a lot of effort finding out how much an item will cost. Now we
need to set our price, based on our cost, across all the territories we
sell in to. 

Pricing takes place if the cost of an item changes. Once we know the cost
of the item (which technically would only be after an Invoice has been typed up 
- discussed in "Invoicing" above), we can price it in UK, EU, US, etc. 

## Daily Pricing

Daily pricing is more of a reactive process. Every day, a team of people 
will research and compare pricing on Amazon. Competitors sell the same item,
and if your price is not the lowest, sales will drop off considerably. 

The daily pricing system allows a user to type in a new, competative price 
against a product. The system will use its understanding of the landed cost
for that item and evaluate if that price is viable. If it is, someone can 
go ahead and change the price on Amazon. 

