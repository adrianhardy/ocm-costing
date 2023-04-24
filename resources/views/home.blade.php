@extends('layouts.app')
@section('layout.breadcrumbs', Breadcrumbs::render('home') )
@push('layout.footer.scripts')
<script type="text/javascript">
        function jumpToYear(year) {
            window.location.href = '/home?year=' + year;
        }
</script>
@endpush
@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between w-100">
                @can('china')
                <div>Sales Order Progress Dashboard</div>
                @endcan
                @can('uk')
                <div>Order Acknowledgement Progress Dashboard</div>
                @endcan
                <div>
                    <select name="year" class="form-control form-control-sm" onchange="jumpToYear(this.value)">
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ $current_year == $year?"selected":"" }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table">
                <tr class="text-center">
                    @can('china')
                    <th class="w-20 ">
                        Draft<br />
                    </th>
                    <th class="w-20">
                        Quote<br />
                    </th>
                    <th class="w-20">
                        Unconfirmed SO<br />
                    </th>
                    <th class="w-20">
                        Confirmed SO<br />
                    </th>
                    <th class="w-20">
                        Shipped<br />
                    </th>
                    <th></th>
                    @endcan
                    @can('uk')
                            <th class="w-15 ">
                                Draft<br />
                            </th>
                            <th class="w-15">
                                Unmatched<br />
                            </th>
                            <th class="w-15">
                                Active<br />
                            </th>
                            <th class="w-15">
                                Partially Received<br />
                            </th>
                            <th class="w-15">
                                Fully Received<br/>
                            </th>
                            <th class="w-15">
                                Closed<br/>
                            </th>
                    @endcan
                </tr>
                <tr>
                    @can('uk')
                    <td>
                        @foreach ($orders['oa_draft'] as $order)
                            <a class="btn btn-primary btn-block mb-1" href="{{ route('order-acknowledgements.edit', $order) }}">{{ $order->reference }}</a>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($orders['oa_unmatched_products'] as $order)
                            <a class="btn btn-primary btn-block mb-1" href="{{ route('order-acknowledgements.edit', $order) }}">{{ $order->reference }}</a>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($orders['oa_active'] as $order)
                            <a class="btn btn-primary btn-block mb-1" href="{{ route('order-acknowledgements.edit', $order) }}">{{ $order->reference }}</a>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($orders['oa_partially_received'] as $order)
                            <a class="btn btn-primary btn-block mb-1" href="{{ route('order-acknowledgements.edit', $order) }}">{{ $order->reference }}</a>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($orders['oa_fully_received'] as $order)
                            <a class="btn btn-primary btn-block mb-1" href="{{ route('order-acknowledgements.edit', $order) }}">{{ $order->reference }}</a>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($orders['oa_closed'] as $order)
                            <a class="btn btn-primary btn-block mb-1" href="{{ route('order-acknowledgements.edit', $order) }}">{{ $order->reference }}</a>
                        @endforeach
                    </td>
                    @endcan
                    @can('china')
                    <td>
                        @foreach ($orders['draft'] as $order)
                            <a class="btn btn-primary btn-block mb-1"
                               href="{{ route('sales-orders.edit', $order) }}">{{ $order->reference }}</a>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($orders['quote'] as $order)
                            <a class="btn btn-primary btn-block mb-1"
                               href="{{ route('sales-orders.edit', $order) }}">{{ $order->reference }}</a>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($orders['sales_order'] as $order)
                            <a class="btn btn-primary btn-block mb-1"
                               href="{{ route('sales-orders.edit', $order) }}">{{ $order->reference }}</a>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($orders['confirmed_sales_order'] as $order)
                            <a class="btn btn-primary btn-block mb-1"
                               href="{{ route('sales-orders.edit', $order) }}">{{ $order->reference }}</a>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($orders['shipments'] as $order)
                            <div class="dropdown">
                            <button class="btn btn-primary btn-block mb-1 dropdown-toggle" data-toggle="dropdown">
                                <span class="badge badge-light">{{ $order->consignments->count() }}</span>
                                {{ $order->reference }}
                            </button>
                            <div class="dropdown-menu">
                                @foreach ($order->consignments as $consignment)
                                <div class="dropdown-item">
                                    <a href="{{ route('shipments.show', $consignment) }}">
                                        {{ $consignment->vessel_name }} {{ $consignment->reference }}
                                    </a>
                                    <div class="text-muted small">
                                        <div class="row">
                                            <div class="col-5">ETD:</div>
                                            <div class="col-7">
                                                {{
                                                    $consignment->estimated_departure_at?
                                                    $consignment->estimated_departure_at->format('d/m/Y'):
                                                    'Not set'
                                                 }}
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-5">ETA:</div>
                                            <div class="col-7">
                                                {{
                                                    $consignment->estimated_arrival_at?
                                                    $consignment->estimated_departure_at->format('d/m/Y'):
                                                    'Not set'
                                                 }}
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-5">Shipped:</div>
                                            <div class="col-7">
                                                {{
                                                    $consignment->departed_at?
                                                    $consignment->departed_at->format('d/m/Y'):
                                                    'Not set'
                                                 }}
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-5">Booked In:</div>
                                            <div class="col-7">
                                                {{
                                                    $consignment->booked_in_at?
                                                    $consignment->booked_in_at->format('d/m/Y'):
                                                    'Not set'
                                                 }}
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-5">Delivered:</div>
                                            <div class="col-7">
                                                {{
                                                    $consignment->docked_at?
                                                    $consignment->docked_at->format('d/m/Y'):
                                                    'Not set'
                                                 }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            </div>
                        @endforeach
                    </td>
                    @endcan
                </tr>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-header">Global Notes</div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-sm-8">
                    <table class="table table-striped table-sm">
                        <tr>
                            <th>Author</th>
                            <th>Comment</th>
                        </tr>
                        @forelse ($comments as $comment)
                            <tr>
                                <td class="w-15 text-nowrap">
                                    {{ $comment->commentator->name }}<br />
                                    <small>{{ $comment->created_at->format('d/M/Y H:i') }}</small>
                                </td>
                                <td>
                                    {{ $comment->comment }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">No comments</td>
                            </tr>
                        @endforelse
                    </table>
                    {!! $comments->links() !!}
                </div>
                <div class="col-12 col-sm-4">
                    {!! Form::open(['url' => route('global-notes.store')]) !!}
                        <div class="form-group mt-1">
                            {!! Form::textarea('comment', null, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Add a new comment']) !!}
                        </div>
                        <div>
                            <button type="submit" class="btn btn-block btn-primary btn-lg">Save</button>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
