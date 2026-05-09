@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Employees</h3>
    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Employee</a>
</div>

<div class="card premium-table mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>ID</th>
                        <th>Department</th>
                        <th style="width: 250px;">Profile Completion</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center">
                                <img src="{{ $employee->photo_url }}" class="rounded-circle me-3 border" width="45" height="45">
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $employee->full_name }}</h6>
                                    <small class="text-muted">{{ $employee->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $employee->employee_id }}</span></td>
                        <td>{{ $employee->department ? $employee->department->name : 'N/A' }}</td>
                        <td>
                            @php $comp = $employee->profile_completion; @endphp
                            <div class="d-flex align-items-center">
                                <div class="progress w-100 me-2" style="height: 6px;">
                                    <div class="progress-bar {{ $comp == 100 ? 'bg-success' : ($comp > 50 ? 'bg-primary' : 'bg-warning') }}" role="progressbar" style="width: {{ $comp }}%;"></div>
                                </div>
                                <small class="text-muted fw-bold" style="min-width: 35px">{{ $comp }}%</small>
                            </div>
                        </td>
                        <td>
                            @if($employee->status === 'active')
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">Active</span>
                            @elseif($employee->status === 'terminated')
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2">Terminated</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2">{{ ucfirst($employee->status) }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light p-2" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li><a class="dropdown-item" href="{{ route('admin.employees.show', $employee) }}"><i class="fas fa-eye text-primary me-2"></i> View Profile</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.employees.edit', $employee) }}"><i class="fas fa-edit text-warning me-2"></i> Edit Details</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Are you sure you want to terminate this employee?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger"><i class="fas fa-user-times me-2"></i> Terminate</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-users fa-3x mb-3 text-light"></i>
                            <p class="mb-0">No employees found. Add your first employee.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($employees->hasPages())
    <div class="card-footer bg-white border-top p-3">
        {{ $employees->links() }}
    </div>
    @endif
</div>
@endsection
