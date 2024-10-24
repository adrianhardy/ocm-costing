@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <price-list
        index-Url="/api/price-list"
        :euro-countries='{!! json_encode($euro_countries, JSON_HEX_QUOT|JSON_HEX_APOS) !!}'
        :row-countries='{!! json_encode($row_countries, JSON_HEX_QUOT|JSON_HEX_APOS) !!}'
        :non-euro-eu-countries='{!! json_encode($non_euro_eu_countries, JSON_HEX_QUOT|JSON_HEX_APOS) !!}'
        :uk='{!! json_encode($uk, JSON_HEX_QUOT|JSON_HEX_APOS) !!}'
        :amazon-digital-tax-pc='{!! json_encode(floatval(setting('amazon_digital_tax_pc', 0))) !!}'
        @can('pricing_approve')
        is-approver
        @endcan
        @can('skip_pricing_process')
        is-pricing-skipper
        @endcan
        @can('__china')
        is-china
        @endcan
        >
            <template #name>
                <h2>Price List</h2>
            </template>
        </price-list>
    </div>
@endsection
