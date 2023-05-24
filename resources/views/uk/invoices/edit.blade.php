@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <edit-invoice
                invoice-id="{{$invoice->id}}"
                @can('approve_invoice')
                    can-approve
                @endcan
        ></edit-invoice>
    </div>
@endsection
