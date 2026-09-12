<div class="mb-3">
    <label class="form-label">Owner</label>
    <select name="owner_id" class="form-select" required>
        <option value="">Select an owner…</option>
        @foreach($owners as $o)
            <option value="{{ $o->id }}" {{ old('owner_id', $property->owner_id ?? '') == $o->id ? 'selected' : '' }}>{{ $o->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Property name</label>
    <input type="text" name="name" value="{{ old('name', $property->name ?? '') }}" class="form-control" required>
</div>
<div class="mb-3">
    <label class="form-label">Address</label>
    <input type="text" name="address" value="{{ old('address', $property->address ?? '') }}" class="form-control" required>
</div>
<div class="mb-3">
    <label class="form-label">City</label>
    <input type="text" name="city" value="{{ old('city', $property->city ?? '') }}" class="form-control">
</div>
<div class="row">
    <div class="col-6 mb-3">
        <label class="form-label">Type</label>
        <select name="type" class="form-select" required>
            @foreach(['apartment','house','studio','commercial'] as $t)
                <option value="{{ $t }}" {{ old('type', $property->type ?? '') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-6 mb-3">
        <label class="form-label">Monthly rent (XAF)</label>
        <input type="number" step="1" name="monthly_rent" value="{{ old('monthly_rent', $property->monthly_rent ?? '') }}" class="form-control" required>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select" required>
        @foreach(['vacant','occupied','maintenance'] as $s)
            <option value="{{ $s }}" {{ old('status', $property->status ?? 'vacant') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Notes</label>
    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $property->notes ?? '') }}</textarea>
</div>
