@extends('layouts.app')
@section('content')
    <div class="container">
        <edit-product :product-id="{{ $product->id }}"></edit-product>
    </div>
@endsection
