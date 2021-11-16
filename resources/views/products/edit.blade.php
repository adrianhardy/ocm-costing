@extends('layouts.app')
@section('content')
    <div class="container">
        <edit-product
            @role('supplier')
            is-supplier
            @endrole
            :product-id="{{ $product->id }}"
        ></edit-product>
    </div>
@endsection
