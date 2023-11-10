@extends('layouts.app')
@section('content')
    <div class="container">
        <sales-orders-list
            :years="{{json_encode($years)}}"
            init-stage="{{ request()->query('stage') }}"
            @role('supplier')
            is-supplier
            @endrole
        ></sales-orders-list>
    </div>
@endsection
