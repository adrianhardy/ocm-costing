@extends('layouts.app')
@section('content')
    <div class="container">
        <edit-product
            @role('supplier')
            is-supplier
            @endrole
            @role('super')
            show-costing
            @endrole
            :cubic-metres-to-feet="{{ setting('cubic_metres_to_feet') }}"
            :product-id="{{ $product->id }}"
            :shipping-cost-per-cubic-foot="{{ $shipping_cost_per_cubic_foot }}"
            :exchange-rate-usd="{{ $exchange_rate_usd }}"
            :import-duty-pc="{{ setting('default_import_duty_pc') }}"
            :commission-rate-pc="{{ setting('default_commission_rate_pc') }}"
        ></edit-product>
    </div>
@endsection
