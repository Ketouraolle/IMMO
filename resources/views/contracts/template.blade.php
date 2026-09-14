{{-- Rendered into contracts.body when sent; the tenant signs this exact snapshot. Keep it free of app-only markup. --}}
@php
    $property = $lease->property;
    $cycle = ['monthly' => 'per month', 'quarterly' => 'per quarter', 'yearly' => 'per year'][$lease->billing_cycle] ?? $lease->billing_cycle;
@endphp
<article>
    <h1>Residential Lease Agreement</h1>
    <div class="contract-ref">Ref. {{ $contract->reference }} · Issued {{ ($contract->sent_at ?? now())->format('d F Y') }}</div>

    <h2>1. Parties</h2>
    <dl>
        <dt>Landlord</dt>
        <dd>{{ $property->owner->name }}, represented by EstateHub, the managing agency</dd>
        <dt>Tenant</dt>
        <dd>{{ $lease->tenant->name }} · {{ $lease->tenant->email }}@if($lease->tenant->phone) · {{ $lease->tenant->phone }}@endif</dd>
    </dl>

    <h2>2. Property</h2>
    <dl>
        <dt>Property</dt>
        <dd>{{ $property->name }} ({{ ucfirst($property->type) }})</dd>
        <dt>Address</dt>
        <dd>{{ $property->address }}@if($property->city), {{ $property->city }}@endif</dd>
    </dl>

    <h2>3. Term</h2>
    <p>
        The lease starts on <strong>{{ $lease->start_date->format('d F Y') }}</strong>
        @if($lease->end_date)
            and ends on <strong>{{ $lease->end_date->format('d F Y') }}</strong>, unless renewed in writing by both parties.
        @else
            and continues until either party ends it with at least one (1) month's written notice.
        @endif
    </p>

    <h2>4. Rent</h2>
    <p>
        The tenant agrees to pay a rent of <strong>{{ number_format($lease->rent_amount) }} XAF {{ $cycle }}</strong>, in advance and no later
        than the 5th day of each period, by Orange Money, MTN MoMo or any other method accepted by the agency. A receipt is issued for every payment received.
    </p>

    <h2>5. Use and upkeep</h2>
    <p>
        The property is let for residential use only. The tenant keeps it clean and in good condition, reports any damage or needed repair promptly
        through EstateHub, and makes no structural changes or sublets without the landlord's written consent.
    </p>

    <h2>6. Landlord's obligations</h2>
    <p>
        The landlord delivers the property in good condition, carries out major repairs not caused by the tenant, and guarantees the tenant's quiet enjoyment of the property.
    </p>

    <h2>7. Termination</h2>
    <p>
        Failure to pay rent for two consecutive periods allows the landlord to end this agreement after written notice. At the end of the lease the tenant returns
        the property and its keys in the condition received, normal wear and tear excepted.
    </p>

    <section data-special-conditions @if(blank($contract->special_conditions)) hidden @endif>
        <h2>Special conditions</h2>
        <p style="white-space: pre-line;" data-special-conditions-text>{{ $contract->special_conditions }}</p>
    </section>

    <h2>Electronic signature</h2>
    <p>
        By signing electronically, the tenant confirms having read and accepted every clause of this agreement. The signature is recorded together with the date,
        time and device details, and is linked to this exact version of the document.
    </p>
    <p style="font-size: .8rem; color: #6b7280;">Prepared by {{ $contract->creator?->name ?? 'EstateHub' }} for EstateHub.</p>
</article>
