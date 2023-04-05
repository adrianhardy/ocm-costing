@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <edit-order-acknowledgements
                acknowledgement-id="{{$orderAcknowledgement->id}}"
        >

        </edit-order-acknowledgements>
    </div>
@endsection
