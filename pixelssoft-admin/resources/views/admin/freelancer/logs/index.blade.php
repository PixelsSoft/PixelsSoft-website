@extends('admin.layout')

@section('title', 'Freelancer API Logs')

@section('content')
<div class="page-header" style="margin-bottom:1rem;"><h1 style="margin:0;">API Logs</h1></div>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Time</th><th>Method</th><th>Endpoint</th><th>Code</th><th>ms</th><th>Success</th><th>Error</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->method }}</td>
                        <td>{{ $log->endpoint }}</td>
                        <td>{{ $log->status_code }}</td>
                        <td>{{ $log->response_time_ms }}</td>
                        <td>{{ $log->success ? 'Yes' : 'No' }}</td>
                        <td>{{ $log->error_message }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">No logs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem;">{{ $logs->links() }}</div>
</div>
@endsection
