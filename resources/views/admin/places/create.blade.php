@extends('layouts.app')
@section('title', 'Create Place')
@section('content')
    <h3 style="margin-top:0">
        Create Place</h3>
    <div class="row container-lg px-4">
        <form method="post" action="{{ route('admin.places.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="type" class="form-label">Type</label>
                    <select name="type_id" id="type_id" class="form-control @error('type_id') is-invalid @enderror" style="width:100%">
                        <option value="">Select Type</option>
                        @foreach ($types as $t)
                            <option value="{{ $t->id }}" {{ old('type_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>


            <div class="row" style="margin-top:12px">
                <div class="col-md-6">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address') }}">
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row" style="margin-top:12px">
                <div class="col-md-6">
                    <label for="icon" class="form-label">Icon (filename)</label>
                    <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" value="{{ old('icon') }}" placeholder="marker-blue.svg">
                    @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="thumbnail" class="form-label">Thumbnail (image)</label>
                    <input type="file" class="form-control @error('thumbnail') is-invalid @enderror" id="thumbnail" name="thumbnail" accept="image/*">
                    @error('thumbnail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row" style="margin-top:12px">
                <div class="col-md-6">
                    <label for="time" class="form-label">Time (opening hours)</label>
                    <input type="text" class="form-control @error('time') is-invalid @enderror" id="time" name="time" value="{{ old('time') }}">
                    @error('time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                        <option value="active" {{ old('status','active')=='active' ? 'selected' : '' }}>active</option>
                        <option value="inactive" {{ old('status')=='inactive' ? 'selected' : '' }}>inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row" style="margin-top:12px">
                <div class="col-md-6">
                    <label for="lat" class="form-label">Latitude</label>
                    <input type="text" class="form-control @error('lat') is-invalid @enderror" id="lat" name="lat" value="{{ old('lat') }}">
                    @error('lat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="lng" class="form-label">Longitude</label>
                    <input type="text" class="form-control @error('lng') is-invalid @enderror" id="lng" name="lng" value="{{ old('lng') }}">
                    @error('lng')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row" style="margin-top:12px">
                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div style="margin-top:16px;display:flex;gap:8px;align-items:center">
                <button class="btn btn-primary" type="submit">Save</button>
                <a class="btn btn-secondary" href="{{ route('admin.places.index') }}">Cancel</a>
            </div>

        </form>
    </div>
    </div>

    {{-- Select2 assets and init --}}
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function(){
        try{
            // initialize select2 on type select (no jQuery include here - layout already loads jQuery)
            $('#type_id').select2({ placeholder: 'Select Type', allowClear: true, width: '100%' });
        }catch(e){ console.warn('Select2 init error', e); }
    });
</script>
@endpush
@endsection
