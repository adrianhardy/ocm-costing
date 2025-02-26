# Sales Orders

## Objective

This project started as a simple way to transcribe the current, existing Excel 
Sales Orders into an online format. This is so that Will Strettle could 
de-skill the process of preparing Sales Orders for their manufacturing partners
in China.

The original intention was also so that manufacturers could log in and amend
the Sales Orders, but that didn't seem to pan out as a viable journey.

The UI for Sales Orders is similar to RAF - pretty sophisticated. Each order
line needs to specify the product, its instructions, the unit cost, the unit
dimensions, the unit weight, the packaging dimensions - everything to help
make sure that the factory build the right thing and deliver the right 
quantity.

## Key Classes

 - `Controllers\Admin\Imports\RunController`
 - `Controllers\Admin\Imports\PreviewController`
 - `Commands\ImportProductsCommand`
 - `Commands\TransformPickingListFileCommand`
 - `Domain\Excel\ParseProductSheet` 

The process of building a sales order is straight forward, from a code 
perspective. `Api\SalesOrders\SalesOrderController`. All the hard work 
has been done in getting the product database into shape. 

A lot of work is done on the front end to make the user comfortable putting 
together a sales order. For example, the total (estimated) number of
cartons is automatically calculated. And the total cubic footage of the order
is summarised at the bottom. If you know a 40ft shipping container can
hold 2,387 cubic feet, then you can make sure that you're maxing out the 
container when you place the order.

## Stages

There used to be a complex "Stage" system for SalesOrders but it's now 
simplified to "Draft", "Unconfirmed", "Confirmed". A user can also 
"finalise" the SO to prevent edits from other users. 

## Order Acknowledgements

Order Acknowledgements are raised and settled by the "UK" side of this system.

For the UK side, they don't need to calculate shipping based on cost per cubic
foot because there's no international element to this. They can simply raise an
order for some items from a UK supplier.

Order Acknowledgements act like a Purchase Order and a Purchase Order is an 
invitation to raise an invoice. The prices of UK-sourced products are set 
because the Invoice from the supplier is tapped into the system. We match up
the Product, apply the price and then `products.unit_price_gbp` is set.

Some Sales Orders (from the China side) appear in the list of Order 
Acknowledgements. These are blended in to the UK Order Acknowledgements 
because, behind the scenes, Order Acknowledgements are built on the same models
as Sales Orders. 

Similarly, if an Invoice is raised and approved for a China-originated product, 
then `products.unit_price_gbp` is set authoritatively.  



