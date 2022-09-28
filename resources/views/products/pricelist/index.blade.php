@extends('layouts.app')
@section('content')
    <div class="container">
        <price-list
        index-Url="/api/price-list"
        :euro-countries='{!! json_encode($euro_countries, JSON_HEX_QUOT|JSON_HEX_APOS) !!}'
        :row-countries='{!! json_encode($row_countries, JSON_HEX_QUOT|JSON_HEX_APOS) !!}'
        :non-euro-eu-countries='{!! json_encode($non_euro_eu_countries, JSON_HEX_QUOT|JSON_HEX_APOS) !!}'
        :uk='{!! json_encode($uk, JSON_HEX_QUOT|JSON_HEX_APOS) !!}'
        @role('pricing_approver')
        is-approver
        @endrole
        >
            <template #name>
                <h2>Price List</h2>
            </template>
        </price-list>
    </div>
@endsection
