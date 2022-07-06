@extends('layouts.app')
@section('content')
    <div class="container">
        <price-list
                index-Url="/api/price-list"
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
