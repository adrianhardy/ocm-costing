@component('mail::message')
# New Inbound Shipment

Hi,

The ETA {{ $this->consignment->supplier_reference }} is now
{{ $this->consignment->estimated_arrival_at->format('d/m/Y') }}.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
