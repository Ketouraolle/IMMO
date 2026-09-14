<div class="mb-3">
    <label class="form-label">{{ __('Owner') }}</label>
    <select name="owner_id" class="form-select" required>
        <option value="">{{ __('Select an owner…') }}</option>
        @foreach($owners as $o)
            <option value="{{ $o->id }}" {{ old('owner_id', $property->owner_id ?? '') == $o->id ? 'selected' : '' }}>{{ $o->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">{{ __('Property name') }}</label>
    <input type="text" name="name" value="{{ old('name', $property->name ?? '') }}" class="form-control" required>
</div>
<div class="mb-3">
    <label class="form-label">{{ __('Address') }}</label>
    <input type="text" name="address" value="{{ old('address', $property->address ?? '') }}" class="form-control" required>
</div>
<div class="mb-3">
    <label class="form-label">{{ __('City') }}</label>
    <input type="text" name="city" value="{{ old('city', $property->city ?? '') }}" class="form-control">
</div>
<div class="row">
    <div class="col-6 mb-3">
        <label class="form-label">{{ __('Type') }}</label>
        <select name="type" class="form-select" required>
            @foreach(['apartment','house','studio','commercial'] as $t)
                <option value="{{ $t }}" {{ old('type', $property->type ?? '') == $t ? 'selected' : '' }}>{{ __(ucfirst($t)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-6 mb-3">
        <label class="form-label">{{ __('Monthly rent (XAF)') }}</label>
        <input type="number" step="1" name="monthly_rent" value="{{ old('monthly_rent', isset($property) ? (int) $property->monthly_rent : '') }}" class="form-control" required>
    </div>
</div>
<div class="row">
    <div class="col-6 mb-3">
        <label class="form-label">{{ __('Visit fee (XAF)') }}</label>
        <input type="number" step="1" min="0" name="visit_fee" value="{{ old('visit_fee', isset($property) ? (int) $property->visit_fee : 0) }}" class="form-control" required>
        <div class="form-text">{{ __('Paid by visitors online or at the visit. 0 = free.') }}</div>
    </div>
    <div class="col-6 mb-3">
        <label class="form-label">{{ __('Commission') }}</label>
        <div class="input-group">
            <input type="number" step="0.5" min="0" max="100" name="commission_rate" value="{{ old('commission_rate', isset($property) ? (float) $property->commission_rate : 10) }}" class="form-control" required>
            <span class="input-group-text">%</span>
        </div>
        <div class="form-text">{{ __('Agency share of each rent payment.') }}</div>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">{{ __('Status') }}</label>
    <select name="status" class="form-select" required>
        @foreach(['vacant','occupied','maintenance'] as $s)
            <option value="{{ $s }}" {{ old('status', $property->status ?? 'vacant') == $s ? 'selected' : '' }}>{{ __(ucfirst($s)) }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">{{ __('Notes') }}</label>
    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $property->notes ?? '') }}</textarea>
</div>
