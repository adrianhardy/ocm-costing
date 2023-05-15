@extends('layouts.app')
@section('content')
    <div class="container">
        <products-list
            @role('supplier')
                is-supplier
            @endrole
            @can('__uk')
                is-uk
            @endcan
        ></products-list>
    </div>
@endsection
