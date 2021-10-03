@component('mail::message')
# Sales Order Invitation

Hi {{ $token->user->name }},

You have been invited to collaborate on a Sales Order.

@component('mail::button', ['url' => route('sales-order-token-login', $token->token)])
    View Sales Order
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
