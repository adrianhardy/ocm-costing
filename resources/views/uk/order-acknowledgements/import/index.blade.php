@extends('layouts.app')
@section('content')
    <div class="container">
        <order-lines-importer :order-acknowledgement-id="{{$orderAcknowledgement->id}}"></order-lines-importer>
    </div>
@endsection