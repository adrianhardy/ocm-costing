@component('mail::message')
# {{ $salesOrder->reference }} / {{ $salesOrder->supplier_reference }}

{{ $user->name }} has just marked the above document as finalised.

You can now go and review that sales order at:

{{ route('sales-orders.edit', $this->salesOrder) }}

@component('mail::button', ['url' => route('sales-orders.edit', $this->salesOrder)])
    View Sales Order
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
