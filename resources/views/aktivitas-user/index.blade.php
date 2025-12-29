@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Aktivitas User</h1>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table id="table_logs" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width:70px">No</th>
                                <th style="width:180px">User</th>
                                <th>Before</th>
                                <th>After</th>
                                <th style="width:180px">Description</th>
                                <th style="width:170px">Log At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        {{ $log->causer?->name ?? '-' }}
                                    </td>

                                    <td>
                                        @if (!empty($log->changes['old']) && is_array($log->changes['old']))
                                            <div style="max-width:520px; white-space:normal;">
                                                @foreach ($log->changes['old'] as $key => $val)
                                                    <div>
                                                        <span class="badge badge-light">{{ $key }}</span>
                                                        :
                                                        <span>{{ is_array($val) ? json_encode($val) : $val }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if (!empty($log->changes['attributes']) && is_array($log->changes['attributes']))
                                            <div style="max-width:520px; white-space:normal;">
                                                @foreach ($log->changes['attributes'] as $key => $val)
                                                    <div>
                                                        <span class="badge badge-light">{{ $key }}</span>
                                                        :
                                                        <span>{{ is_array($val) ? json_encode($val) : $val }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>{{ $log->description ?? '-' }}</td>

                                    <td>
                                        {{ optional($log->created_at)->format('d-m-Y H:i:s') ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function(){
    // aman kalau kebuka ulang / partial reload
    if ($.fn.DataTable.isDataTable('#table_logs')) {
        $('#table_logs').DataTable().destroy();
    }

    $('#table_logs').DataTable({
        paging: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        autoWidth: false,
        order: [[5, 'desc']], // urutkan dari log terbaru
        columnDefs: [
            { targets: [2,3], orderable:false }, // before/after biasanya panjang
        ]
    });
});
</script>
@endpush
