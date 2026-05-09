@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Leave Management</h3>
</div>

<div class="card premium-table mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Leave Type</th>
                        <th>Date Range</th>
                        <th>Days</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                    <tr>
                        <td class="ps-4 pb-3">
                            <h6 class="mb-0 fw-bold">{{ $leave->employee->full_name }}</h6>
                            <small class="text-muted">{{ $leave->employee->department->name ?? 'N/A' }}</small>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ ucfirst($leave->leave_type) }}</span></td>
                        <td>{{ $leave->from_date->format('d M') }} - {{ $leave->to_date->format('d M, Y') }}</td>
                        <td>{{ $leave->total_days }} day(s)</td>
                        <td style="max-width: 200px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" title="{{ $leave->reason }}">{{ $leave->reason }}</td>
                        <td>
                            @if($leave->status === 'pending') <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($leave->status === 'approved') <span class="badge bg-success">Approved</span>
                            @else <span class="badge bg-danger">Rejected</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            @if($leave->status === 'pending')
                            <form action="{{ route('admin.leaves.approve', $leave) }}" method="POST" class="d-inline">
                                @csrf <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Approve</button>
                            </form>
                            <form action="{{ route('admin.leaves.reject', $leave) }}" method="POST" class="d-inline">
                                @csrf 
                                <input type="hidden" name="rejection_reason" value="Manager rejected">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
                            </form>
                            @else
                                <small class="text-muted">No actions</small>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <p class="mb-0">No leave requests found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($leaves->hasPages())
    <div class="card-footer bg-white border-top p-3">
        {{ $leaves->links() }}
    </div>
    @endif
</div>
@endsection
