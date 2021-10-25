@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <edit-sales-order
                :sales-order-id="{{ $so->id }}"
                :is-super="{!! json_encode(\Auth::user()->hasRole('super')) !!}"

        >

        </edit-sales-order>
    </div>
@endsection
