@extends('admin.layouts.app')

@section('title', 'Ticket Types')
@section('page-title', 'Ticket Types')

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="mb-0">Ticket Type Management</h3>
        <a href="{{ route('admin.ticket-types.create') }}" class="btn btn-primary btn-sm ml-auto">
            <i class="fas fa-plus mr-1"></i> Add Type
        </a>
    </div>

    <div class="card-body table-responsive">
        <table class="table datatable">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Frequency</th>
                    <th>Price</th>
                    <th>1st Prize</th>
                    <th>Status</th>
                    <th style="width:150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($types as $type)
                    <tr>
                        <td>
                            @if($type->image)
                                <img src="{{ Storage::url($type->image) }}" style="height:46px;width:62px;object-fit:cover;border-radius:8px" alt="">
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <strong>{{ $type->name }}</strong><br>
                            <small>{{ $type->festival_name ?: 'Standard draw' }}</small>
                        </td>
                        <td>{{ ucfirst($type->frequency) }}</td>
                        <td>Rs {{ number_format($type->ticket_price, 2) }}</td>
                        <td>Rs {{ number_format(optional($type->prizes->firstWhere('position', 1))->amount, 2) }}</td>
                        <td>
                            <span class="badge badge-{{ $type->is_active ? 'success' : 'secondary' }}">
                                {{ $type->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex" style="gap:6px;">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.ticket-types.edit', $type) }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.ticket-types.destroy', $type) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $types->links() }}
    </div>
</div>
@endsection
