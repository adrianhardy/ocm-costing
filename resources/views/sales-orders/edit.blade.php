@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <edit-sales-order
                :sales-order-id="{{ $so->id }}"
                @role('supplier')
                is-supplier
                @endrole
                @role('super')
                is-super
                @endrole
        >

        </edit-sales-order>
    </div>
@endsection
