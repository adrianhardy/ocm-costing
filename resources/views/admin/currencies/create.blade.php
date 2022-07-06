@extends('layouts.app')
@section('content')
    <div class="container">
        <create-currency
                redirect-url={{ route('currencies.index') }}
        >
            <template #title>Create a new currency</template>
        </create-currency>
    </div>
@endsection
