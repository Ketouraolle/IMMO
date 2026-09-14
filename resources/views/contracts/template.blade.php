{{-- Rendered into contracts.body when sent (in the tenant's language); the tenant signs this exact snapshot. Keep it free of app-only markup. --}}
@php
    $property = $lease->property;
    $cycle = ['monthly' => __('per month'), 'quarterly' => __('per quarter'), 'yearly' => __('per year')][$lease->billing_cycle] ?? $lease->billing_cycle;
@endphp
<article>
    <h1>{{ __('Residential Lease Agreement') }}</h1>
    <div class="contract-ref">{{ __('Ref. :reference · Issued :date', ['reference' => $contract->reference, 'date' => ($contract->sent_at ?? now())->translatedFormat('j F Y')]) }}</div>

    <h2>1. {{ __('Parties') }}</h2>
    <dl>
        <dt>{{ __('Landlord') }}</dt>
        <dd>{{ __(':owner, represented by EstateHub, the managing agency', ['owner' => $property->owner->name]) }}</dd>
        <dt>{{ __('Tenant') }}</dt>
        <dd>{{ $lease->tenant->name }} · {{ $lease->tenant->email }}@if($lease->tenant->phone) · {{ $lease->tenant->phone }}@endif</dd>
    </dl>

    <h2>2. {{ __('Property') }}</h2>
    <dl>
        <dt>{{ __('Property') }}</dt>
        <dd>{{ $property->name }} ({{ __(ucfirst($property->type)) }})</dd>
        <dt>{{ __('Address') }}</dt>
        <dd>{{ $property->address }}@if($property->city), {{ $property->city }}@endif</dd>
    </dl>

    <h2>3. {{ __('Term') }}</h2>
    <p>
        @if($lease->end_date)
            {!! __('The lease starts on :start and ends on :end, unless renewed in writing by both parties.', [
                'start' => '<strong>'.e($lease->start_date->translatedFormat('j F Y')).'</strong>',
                'end' => '<strong>'.e($lease->end_date->translatedFormat('j F Y')).'</strong>',
            ]) !!}
        @else
            {!! __("The lease starts on :start and continues until either party ends it with at least one (1) month's written notice.", [
                'start' => '<strong>'.e($lease->start_date->translatedFormat('j F Y')).'</strong>',
            ]) !!}
        @endif
    </p>

    <h2>4. {{ __('Rent') }}</h2>
    <p>
        {!! __('The tenant agrees to pay a rent of :rent, in advance and no later than the 5th day of each period, by Orange Money, MTN MoMo or any other method accepted by the agency. A receipt is issued for every payment received.', [
            'rent' => '<strong>'.e(number_format($lease->rent_amount).' XAF '.$cycle).'</strong>',
        ]) !!}
    </p>

    <h2>5. {{ __('Use and upkeep') }}</h2>
    <p>{{ __("The property is let for residential use only. The tenant keeps it clean and in good condition, reports any damage or needed repair promptly through EstateHub, and makes no structural changes or sublets without the landlord's written consent.") }}</p>

    <h2>6. {{ __("Landlord's obligations") }}</h2>
    <p>{{ __("The landlord delivers the property in good condition, carries out major repairs not caused by the tenant, and guarantees the tenant's quiet enjoyment of the property.") }}</p>

    <h2>7. {{ __('Termination') }}</h2>
    <p>{{ __('Failure to pay rent for two consecutive periods allows the landlord to end this agreement after written notice. At the end of the lease the tenant returns the property and its keys in the condition received, normal wear and tear excepted.') }}</p>

    <section data-special-conditions @if(blank($contract->special_conditions)) hidden @endif>
        <h2>{{ __('Special conditions') }}</h2>
        <p style="white-space: pre-line;" data-special-conditions-text>{{ $contract->special_conditions }}</p>
    </section>

    <h2>{{ __('Electronic signature') }}</h2>
    <p>{{ __('By signing electronically, the tenant confirms having read and accepted every clause of this agreement. The signature is recorded together with the date, time and device details, and is linked to this exact version of the document.') }}</p>
    <p style="font-size: .8rem; color: #6b7280;">{{ __('Prepared by :name for EstateHub.', ['name' => $contract->creator?->name ?? 'EstateHub']) }}</p>
</article>
