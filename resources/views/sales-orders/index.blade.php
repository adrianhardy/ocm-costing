@extends('layouts.app')
@section('content')
    <div class="container">
        <sales-orders-list
            init-stage="{{ request()->query('stage') }}"
            @role('supplier')
            is-supplier
            @endrole
        ></sales-orders-list>
    </div>
@endsection
