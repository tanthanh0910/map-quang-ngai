@extends('layouts.app')
@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div class="me-3">
                <form method="GET" class="d-flex">
                    <div class="input-group">
                        <div class="col">
                            <input name="q" class="form-control" value="{{ request('q') }}" placeholder="Search places"/>
                        </div>
                        <div class="col" style="margin-left: 10px">
                            <select name="type_id" class="form-select">
                                <option value="">All types</option>
                                @foreach($types as $t)
                                    <option value="{{ $t->id }}" @if(request('type_id')==$t->id) selected @endif>{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            <a href="{{ route('admin.places.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i>
                Thêm mới
            </a>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: #f3f4f7;">
                        <tr class="border-bottom">
                            <th>ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Priority</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr class="align-middle">
                            <td class="py-3">{{ $item->id }}</td>
                            <td class="py-3">{{ $item->name }}</td>
                            <td class="py-3">{{ optional($item->type)->name }}</td>
                            <td class="py-3">{{ $item->priority ?? '' }}</td>
                            <td class="py-3 text-end">
                                <div class="dropdown">
                                    <button class="btn btn-link ma-actions-btn p-0" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.places.edit', $item->id) }}">
                                                <i class="bi bi-pencil"></i>
                                                Edit
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.places.destroy', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this place?')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
        @if($items->hasPages())
            {!! $items->withQueryString()->links('partials.pagination') !!}
        @endif
    </div>
</div>
@endsection
