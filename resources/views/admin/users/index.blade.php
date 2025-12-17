@extends('layouts.app')
@section('title','User ')
@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div class="me-3">
                <form method="GET" class="d-flex">
                    <div class="input-group">
                        <div class="col">
                            <input name="q" class="form-control" value="{{ request('q') }}" placeholder="Search users"/>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
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
                            <th>Email</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr class="align-middle">
                            <td class="py-3">{{ $item->id }}</td>
                            <td class="py-3">{{ $item->name }}</td>
                            <td class="py-3">{{ $item->email }}</td>
                            <td class="py-3 text-end">
                                <div class="dropdown">
                                    <button class="btn btn-link ma-actions-btn p-0" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.users.edit', $item->id) }}">
                                                <i class="bi bi-pencil"></i>
                                                Edit
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.users.destroy', $item->id) }}" method="POST" class="d-inline">
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

