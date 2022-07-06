@extends('layouts.app')
@section('content')
    <div class="container">
        <edit-shipment :shipment-id="{{$shipment->id}}">

        </edit-shipment>
    </div>
@endsection
