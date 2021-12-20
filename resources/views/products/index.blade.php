@extends('layouts.app')
@section('content')
    <div class="container">
        <products-list
            @role('supplier')
                is-supplier
            @endrole
        ></products-list>
    </div>
@endsection
