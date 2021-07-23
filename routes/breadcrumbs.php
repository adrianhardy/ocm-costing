<?php



// Home
Breadcrumbs::for('home', function ($trail) {
    $trail->push('Home', route('home'));
});

Breadcrumbs::for('items.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Items', route('items.index'));
});

Breadcrumbs::for('amazon.upload', function ($trail) {
    $trail->parent('home');
    $trail->push('Amazon');
    $trail->push('Upload', route('amazon.upload'));
});

Breadcrumbs::for('amazon.suggested-skus.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Amazon');
    $trail->push('Suggested SKUs', route('amazon.suggested-skus.index'));
});

Breadcrumbs::for('amazon.removal-orders', function ($trail) {
    $trail->parent('home');
    $trail->push('Amazon');
    $trail->push('Removal Orders', route('amazon.removal-orders.index'));
});

Breadcrumbs::for('amazon.orders', function ($trail) {
    $trail->parent('home');
    $trail->push('Amazon');
    $trail->push('Orders', route('amazon.orders.index'));
});

Breadcrumbs::for('shipments.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Shipments', route('shipments.index'));
});

Breadcrumbs::for('stocktakes.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Stock Takes', route('stocktakes.index'));
});

Breadcrumbs::for('locations.upload', function ($trail) {
    $trail->parent('home');
    $trail->push('Locations');
    $trail->push('Upload', route('locations.upload'));
});

Breadcrumbs::for('locations.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Locations');
    $trail->push('List', route('locations.index'));
});

Breadcrumbs::for('reason-codes.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Reason Codes');
    $trail->push('List', route('reason-codes.index'));
});

Breadcrumbs::for('users.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Users');
    $trail->push('List', route('users.index'));
});

Breadcrumbs::for('suppliers.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Suppliers');
    $trail->push('List', route('suppliers.index'));
});

Breadcrumbs::for('reports.reason-codes', function ($trail) {
    $trail->parent('home');
    $trail->push('Reports');
    $trail->push('Reason Codes Report', route('reports.reason-codes.show'));
});

Breadcrumbs::for('reports.goods-in', function ($trail) {
    $trail->parent('home');
    $trail->push('Reports');
    $trail->push('Goods In Report', route('reports.goods-in.show'));
});

Breadcrumbs::for('reports.re-order', function ($trail) {
    $trail->parent('home');
    $trail->push('Reports');
    $trail->push('ReOrder Report', route('reports.re-order-level.show'));
});
