@extends('layouts.app')
@section('title','Create Type')
@section('content')
    <h2>Create Type</h2>
    <div class="row container-lg px-4">
        <form method="post" action="{{ route('admin.types.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                        name="name" value="{{ old('name') }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="icon" class="form-label">Icon</label>
                    <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon"
                        name="icon" value="{{ old('icon') }}">
                    @error('icon')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <label for="priority" class="form-label">Priority</label>
                    <input type="number" value="0" class="form-control @error('priority') is-invalid @enderror" id="priority"
                        name="priority" value="{{ old('priority') }}">
                    @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div style="margin-top:16px;display:flex;gap:8px;align-items:center">
                <button class="btn btn-primary" type="submit">Save</button>
                <a class="btn btn-secondary" href="{{ route('admin.types.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
