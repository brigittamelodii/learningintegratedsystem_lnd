@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-3" style="color:hsl(216, 98%, 52%); text-align: center;"><strong>Training Need Analysis (TNA)
                List</strong></h2>

        {{-- Flash message --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="col">
            <div class="col d-flex justify-content-between align-items-center">
                {{-- Filter Form --}}
                <form method="GET" action="{{ route('tna.index') }}" class="row g-3 mb-4">
                    <div class="col-auto">
                        <input type="text" name="tna_year" class="form-control" placeholder="Search by year"
                            value="{{ request('tna_year') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">🔍 Search</button>
                        <a href="{{ route('tna.index') }}" class="btn btn-secondary">🔄 Reset</a>
                    </div>
                </form>

                {{-- Button to create new TNA --}}
                @hasanyrole('manager|superadmin|executive')
                    <a href="{{ route('tna.create') }}" class="btn btn-success mb-3"> Create New TNA</a>
                @endhasanyrole
            </div>
        </div>
        {{-- TNA table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Tahun</th>
                        {{-- <th>Training Plan File</th> --}}
                        <th>Investment Projection</th>
                        <th>Realization</th>
                        <th>Remaining Budget</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tnas as $index => $tna)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $tna->tna_year }}</td>
                            {{-- <td>
                                <!-- Tombol untuk buka modal -->
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#documentModal{{ $tna->id }}">
                                    📄 Lihat Dokumen
                                </button>
                            </td> --}}
                            <td>Rp{{ number_format($tna->tna_min_budget, 0, ',', '.') }}</td>
                            <td>
                                @if ($tna->tna_realization)
                                    Rp{{ number_format($tna->tna_realization, 0, ',', '.') }}
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </td>
                            @php
                                $remainingBudget = $tna->tna_min_budget - $tna->tna_realization;
                                $isOver = $tna->tna_realization > $tna->tna_min_budget;
                            @endphp

                            <td>
                                <span class="{{ $isOver ? 'text-danger' : 'text-success' }}">
                                    Rp{{ number_format($remainingBudget, 0, ',', '.') }}
                                </span>
                            </td>

                            <td>
                                {{-- Optional: Edit/Delete --}}
                                @hasanyrole('manager|superadmin')
                                    <a href="{{ route('tna.edit', $tna->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                @endhasanyrole
                                <a href="{{ route('tna.view', $tna->id) }}" class="btn btn-primary btn-sm">View</a>
                                @hasanyrole('manager|superadmin')
                                    <form action="{{ route('tna.destroy', $tna->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"> Delete</button>
                                    </form>
                                @endhasanyrole
                            </td>
                        </tr>
                        <!-- Modal -->
                        <div class="modal fade" id="documentModal{{ $tna->id }}" tabindex="-1"
                            aria-labelledby="documentModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl"> <!-- Bisa ganti modal-lg kalau ingin lebih kecil -->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="documentModalLabel">Dokumen TNA - {{ $tna->tna_year }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <iframe src="{{ asset('storage/' . $tna->tna_document) }}" width="100%"
                                            height="600px" frameborder="0"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No TNA data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
