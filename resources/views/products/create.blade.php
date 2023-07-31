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
            @can('__uk')
                is-uk
            @endcan
            @can('__china')
                is-china
            @endcan
            @can('delete_products')
                can-delete
            @endcan
            @can('copy_products')
                can-copy
            @endcan
            @can('update_custom_minimum_profit')
                can-custom-profit
            @endcan
            :shipping-cost-per-cubic-foot="{{ setting('default_import_duty_pc') }}"
            :exchange-rate-usd="{{ $exchange_rate_usd }}"
            :import-duty-pc="{{ setting('default_import_duty_pc') }}"
            :commission-rate-pc="{{ setting('default_commission_rate_pc') }}"
        ></edit-product>
    </div>
@endsection
