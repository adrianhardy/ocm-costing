@component('mail::message')
# Sales Order Invitation

Hi {{ $token->user->name }},

You have been invited to collaborate on a Sales Order.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
