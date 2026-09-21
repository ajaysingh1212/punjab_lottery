@extends('admin.layouts.app')

@section('title', $type->exists ? 'Edit Ticket Type' : 'Add Ticket Type')
@section('page-title', $type->exists ? 'Edit Ticket Type' : 'Add Ticket Type')

@section('content')
<style>
.ticket-config{border:0;border-radius:8px;box-shadow:0 14px 34px rgba(15,23,42,.08)}
.config-head{background:#7c2d12;color:#fff;border-radius:8px 8px 0 0;padding:20px 24px}
.config-head h3{font-weight:900;margin:0}.prize-box{background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:14px}
.form-control,.btn{border-radius:8px}.btn{font-weight:800}.section-title{font-weight:900;margin:8px 0 14px}
</style>

<form method="POST" enctype="multipart/form-data" action="{{ $type->exists ? route('admin.ticket-types.update', $type) : route('admin.ticket-types.store') }}" class="card ticket-config">
    @csrf
    @if($type->exists)
        @method('PUT')
    @endif

    <div class="config-head d-flex justify-content-between align-items-center">
        <div>
            <h3><i class="fas fa-ticket-alt mr-2"></i>Ticket Type Setup</h3>
            <small>Daily, weekly, monthly, and festival draw rules with prize positions.</small>
        </div>
        <div class="d-flex" style="gap:8px;">
            <a href="{{ route('admin.ticket-types.index') }}" class="btn btn-outline-light">Cancel</a>
            <button type="submit" class="btn btn-light"><i class="fas fa-save mr-1"></i>Save Type</button>
        </div>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-4 form-group">
                <label>Name</label>
                <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $type->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 form-group">
                <label>Frequency</label>
                <select name="frequency" class="form-control @error('frequency') is-invalid @enderror">
                    @foreach(['daily','weekly','monthly','festival'] as $freq)
                        <option value="{{ $freq }}" @selected(old('frequency', $type->frequency ?: 'weekly') === $freq)>{{ ucfirst($freq) }}</option>
                    @endforeach
                </select>
                @error('frequency')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 form-group">
                <label>Ticket Price</label>
                <input type="number" step="0.01" min="1" class="form-control @error('ticket_price') is-invalid @enderror" name="ticket_price" value="{{ old('ticket_price', $type->ticket_price) }}" required>
                @error('ticket_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2 form-group">
                <label>Status</label>
                <select class="form-control" name="is_active">
                    <option value="1" @selected((string) old('is_active', $type->is_active ?? 1) === '1')>Active</option>
                    <option value="0" @selected((string) old('is_active', $type->is_active) === '0')>Inactive</option>
                </select>
            </div>
            <div class="col-md-4 form-group">
                <label>Festival Name</label>
                <input class="form-control @error('festival_name') is-invalid @enderror" name="festival_name" value="{{ old('festival_name', $type->festival_name) }}">
                @error('festival_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 form-group">
                <label>Festival Date</label>
                <input type="date" class="form-control @error('festival_date') is-invalid @enderror" name="festival_date" value="{{ old('festival_date', $type->festival_date?->format('Y-m-d')) }}">
                @error('festival_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-5 form-group">
                <label>Ticket Image</label>
                <input type="file" class="form-control @error('image') is-invalid @enderror" name="image" accept="image/*" data-preview="ticket-image-preview">
                @error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <img id="ticket-image-preview" src="{{ $type->image ? Storage::url($type->image) : '' }}" class="mt-2" style="height:72px;border-radius:8px;object-fit:cover;{{ $type->image ? '' : 'display:none;' }}" alt="Ticket image">
            </div>
        </div>

        <div class="section-title">Prize Configuration</div>
        <div class="prize-box">
            <div class="row">
                @for($i = 1; $i <= 10; $i++)
                    <div class="col-md-2 form-group">
                        <label>{{ $i }} Prize</label>
                        <input class="form-control @error('prizes.'.$i) is-invalid @enderror" type="number" step="0.01" name="prizes[{{ $i }}]" value="{{ old('prizes.'.$i, optional($type->prizes->firstWhere('position', $i))->amount) }}" @if($i === 1) required min="1" @else min="0" @endif>
                        @error('prizes.'.$i)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endfor
            </div>
        </div>

        <div class="form-group mt-3">
            <label>Description</label>
            <textarea class="form-control" rows="4" name="description">{{ old('description', $type->description) }}</textarea>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.querySelector('[data-preview="ticket-image-preview"]')?.addEventListener('change', function() {
    var file = this.files && this.files[0];
    var preview = document.getElementById('ticket-image-preview');
    if (!file || !preview) return;
    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';
    preview.onload = function() { URL.revokeObjectURL(preview.src); };
});
</script>
@endpush
@endsection
