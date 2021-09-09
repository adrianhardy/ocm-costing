@extends('layouts.app')
@section('content')
    <div class="container">
        <consignments
        order-id="{{$salesOrder->id}}"
        >
            <template #title>
                <h4>Order #{{$salesOrder->id}} Consignments</h4>
            </template>
        </consignments>
    </div>
@endsection