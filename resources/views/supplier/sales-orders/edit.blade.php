@extends('layouts.app')
@section('content')
    <div class="container">
        <edit-sales-order is-supplier :sales-order-id="{{ $so->id }}"></edit-sales-order>
    </div>
@endsection
