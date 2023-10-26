@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <shipments-list
                :years="{{json_encode($years)}}"
        >
        </shipments-list>
    </div>
@endsection
