@extends('layouts.app')
@section('content')
    <div class="container">
        <consignments
            order-id="{{$salesOrder->id}}"
        >
            <template #title>
                <h4>Order #{{$salesOrder->sales_order_number}} : Shipments</h4>
            </template>
        </consignments>
    </div>
@endsection
