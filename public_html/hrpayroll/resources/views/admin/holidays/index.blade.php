@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Company Holidays</h3>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
        <i class="fas fa-plus"></i> Add Holiday
    </button>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card stat-card h-100">
            <div class="card-body">
                <h5 class="fw-bold text-muted mb-3">Filter by Year</h5>
                <form action="{{ route('admin.holidays.index') }}" method="GET">
                    <div class="btn-group-vertical w-100" role="group">
                        @for($i = now()->year - 1; $i <= now()->year + 2; $i++)
                            <button type="submit" name="year" value="{{ $i }}" class="btn btn-outline-primary text-start {{ $year == $i ? 'active' : '' }}">
                                <i class="fas fa-calendar-alt text-primary d-inline-block me-2"></i> {{ $i }}
                            </button>
                        @endfor
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-9 mb-4">
        <div class="card premium-table h-100">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Day</th>
                                <th>Occasion / Name</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($holidays as $holiday)
                                <tr>
                                    <td class="ps-4 fw-medium">{{ $holiday->date->format('d M, Y') }}</td>
                                    <td>{{ $holiday->date->format('l') }}</td>
                                    <td>{{ $holiday->name }}</td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('admin.holidays.destroy', $holiday) }}" method="POST" onsubmit="return confirm('Remove this holiday?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-calendar-times fs-2 mb-3 text-light"></i><br>
                                        No holidays defined for {{ $year }} yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addHolidayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add New Holiday</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.holidays.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Holiday Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Diwali, Christmas" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Holiday</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
