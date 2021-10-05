@extends('layouts.app')
@section('content')
    <div class="container">
        <edit-inspection-report-modal :auto-open="true" :inspection-report-id="{{ $inspection->id }}"></edit-inspection-report-modal>
    </div>
@endsection
