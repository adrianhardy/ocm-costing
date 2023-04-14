@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <list-daily-pricing
                @can('pricing_approve')
                    can-approve
                @endcan
                @can('daily_pricing_export')
                    can-export
                @endcan
        >

        </list-daily-pricing>
    </div>
@endsection