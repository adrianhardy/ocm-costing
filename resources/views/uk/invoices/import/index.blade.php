@extends('layouts.app')
@section('content')
    <div class="container">
        <invoice-importer :order-acknowledgement-id="{{$orderAcknowledgement->id}}"></invoice-importer>
    </div>
@endsection