@extends('layouts.app')

@section('content')
    <div class="container">
        @can('china')
        <create-import
            :types='{!! json_encode($types, JSON_HEX_APOS) !!}'
            :columns='{!! json_encode($columns, JSON_HEX_APOS) !!}'
            :suppliers='{!! json_encode($suppliers, JSON_HEX_APOS) !!}'
            preview-rows="20"
            code="{{ $code }}"
        ></create-import>
        @endcan
        @can('uk')
            <uk-product-importer>

            </uk-product-importer>
        @endcan
    </div>
@endsection
