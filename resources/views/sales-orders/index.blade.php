@extends('layouts.app')
@section('content')
    <div class="container">
        <sales-orders-list
            @role('supplier')
            is-supplier
            @endrole
        ></sales-orders-list>
    </div>
@endsection
