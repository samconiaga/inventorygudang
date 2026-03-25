@extends('layouts.app')

@section('content')

<div class="section-header">
    <h1>Semua Permintaan Barang</h1>

    <div class="ml-auto">
        <a href="{{ route('permintaan.index') }}" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Permintaan Saya
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@endif

<div class="row">
    <div class="col-lg-12">

        <div class="card">
            <div class="card-body">
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="table_permintaan">

                        <thead>
                            <tr>
                                <th style="width:70px">No</th>
                                <th>Kode</th>
                                <th>Pemohon</th>
                                <th style="width:160px">Tanggal</th>
                                <th style="width:130px">Status</th>
                                <th style="width:320px">Opsi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($permintaans as $i => $p)
                                <tr>
                                    <td>{{ $permintaans->firstItem() + $i }}</td>
                                    <td>{{ $p->kode }}</td>
                                    <td>{{ $p->pemohon->name ?? '-' }}</td>
                                    <td>{{ $p->created_at->format('d-m-Y H:i') }}</td>
                                    <td>
                                        @if($p->status == 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif($p->status == 'approved')
                                            <span class="badge badge-info">Approved</span>
                                        @elseif($p->status == 'rejected')
                                            <span class="badge badge-danger">Rejected</span>
                                        @elseif($p->status == 'processed')
                                            <span class="badge badge-success">Processed</span>
                                        @endif
                                    </td>
                                    <td>

                                        <a href="{{ route('permintaan.show',$p->id) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fa fa-eye"></i> Detail
                                        </a>

                                        @if($p->status == 'pending')

                                            <form action="{{ route('permintaan.approve',$p->id) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-success"
                                                    onclick="return confirm('Approve permintaan ini?')">
                                                    <i class="fa fa-check"></i> Approve
                                                </button>
                                            </form>

                                            <form action="{{ route('permintaan.reject',$p->id) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Tolak permintaan ini?')">
                                                    <i class="fa fa-times"></i> Reject
                                                </button>
                                            </form>

                                        @endif

                                        @if($p->status == 'approved')

                                            <form action="{{ route('permintaan.process',$p->id) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-dark"
                                                    onclick="return confirm('Proses & kurangi stok?')">
                                                    <i class="fa fa-box"></i> Proses Barang Keluar
                                                </button>
                                            </form>

                                        @endif

                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Tidak ada data permintaan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                <div class="mt-3">
                    {{ $permintaans->links() }}
                </div>

            </div>
        </div>

    </div>
</div>

@endsection