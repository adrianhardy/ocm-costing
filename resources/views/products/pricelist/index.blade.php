@extends('layouts.app')
@section('content')
    <div class="container">
        <price-list
            index-url="/api/price-list"
            :euro-countries='{!! json_encode($euro_countries) !!}'
            :row-countries='{!! json_encode($row_countries) !!}'
            :uk='{!! json_encode($uk) !!}'
        >
            <template #name>
                <h2>Price List</h2>
            </template>
        </price-list>
    </div>
@endsection
