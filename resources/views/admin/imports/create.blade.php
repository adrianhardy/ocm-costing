@extends('layouts.app')

@section('content')
    <div class="container">
        <create-import
            :types='{!! json_encode($types) !!}'
            :columns='{!! json_encode($columns) !!}'
            :suppliers='{!! json_encode($suppliers) !!}'
            preview-rows="20"
            code="{{ $code }}"
        ></create-import>
    </div>
@endsection
