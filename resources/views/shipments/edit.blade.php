@extends('layouts.app')
@section('content')
    <div class="container">

        <!-- the is super here prop looks pretty weird,
        but it seems to work, as not having true and false as strings
         broke the page for anyone who wasn't a super -->
        <edit-shipment
                :shipment-id="{{$shipment->id}}"
                :is-super="{{auth()->user()->hasRole(['super'])?'true':'false'}}"
        >

        </edit-shipment>
    </div>
@endsection
