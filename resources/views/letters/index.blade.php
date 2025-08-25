@hasanyrole('superadmin|manager|pic')
    @extends('layouts.app')

    @section('content')
        <div class="container my-4">
            <h2 class="text-center text-primary fw-bold" style="margin-top: 10px">Memo</h2>

            <ul class="nav nav-tabs mb-3" id="letterTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-semibold" id="internal-tab" data-bs-toggle="tab" data-bs-target="#internal"
                        type="button" role="tab">
                        📩 Internal Memo
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold" id="external-tab" data-bs-toggle="tab" data-bs-target="#external"
                        type="button" role="tab">
                        📬 External Memo
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="letterTabsContent">

                <!-- Internal Letters -->
                <div class="tab-pane fade show active" id="internal" role="tabpanel">
                    <div class="row g-2 align-items-center mb-3">
                        {{-- Form Filter Internal --}}
                        <div class="col-lg-10 col-md-9">
                            <form method="GET" class="row g-2">
                                <div class="col-md-4">
                                    <input type="text" name="search_internal" value="{{ request('search_internal') }}"
                                        class="form-control" placeholder="Cari internal...">
                                </div>
                                <div class="col-md-3">
                                    <select name="year_internal" class="form-select">
                                        <option value="">- Pilih Tahun -</option>
                                        @foreach (range(date('Y'), 2020) as $year)
                                            <option value="{{ $year }}"
                                                {{ request('year_internal') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="program_internal" class="form-select">
                                        <option value="">- Pilih Program -</option>
                                        @foreach ($programs as $program)
                                            <option value="{{ $program->id }}"
                                                {{ request('program_internal') == $program->id ? 'selected' : '' }}>
                                                {{ $program->program_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex gap-2">
                                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Reset</a>
                                </div>
                            </form>
                        </div>

                        {{-- Tombol Create --}}
                        <div class="col-lg-2 col-md-3 text-end">
                            <a href="{{ route('internal-letters.create') }}" class="btn btn-primary w-100">Create</a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped align-middle">
                            <thead class="table-light text-nowrap">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nomor Memo</th>
                                    <th style="width: 40%">Perihal</th>
                                    <th>PIC</th>
                                    <th colspan="2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($internalLetters as $internal)
                                    <tr @if (!$internal->letter_document) class="table-warning" @endif>
                                        <td>{{ \Carbon\Carbon::parse($internal->letter_date)->format('d M Y') }}</td>
                                        <td>{{ $internal->letter_no }}</td>
                                        <td>{{ $internal->subject }}</td>
                                        <td>
                                            {{ $internal->user ? ucwords(str_replace('.', ' ', Str::before($internal->user->email, '@'))) : 'Unknown' }}
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                @if ($internal->letter_document)
                                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                        data-bs-target="#docModalInternal{{ $internal->id }}">
                                                        View
                                                    </button>
                                                @else
                                                    <span class="badge bg-secondary">No Doc</span>
                                                @endif
                                                <a href="{{ route('internal-letters.edit', $internal->id) }}"
                                                    class="btn btn-sm btn-warning">Edit</a>
                                                @if (!$internal->letter_document)
                                                    <form action="{{ route('internal-letters.destroy', $internal->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Yakin ingin menghapus nomor memo ini?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4"><em>Tidak ada memo internal.</em>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- External Letters -->
                <div class="tab-pane fade" id="external" role="tabpanel">
                    <div class="row g-2 align-items-center mb-3">
                        {{-- Form Filter --}}
                        <div class="col-lg-10 col-md-9">
                            <form method="GET" class="row g-2">
                                <div class="col-md-4">
                                    <input type="text" name="search_external" value="{{ request('search_external') }}"
                                        class="form-control" placeholder="Cari eksternal...">
                                </div>
                                <div class="col-md-3">
                                    <select name="year_external" class="form-select">
                                        <option value="">- Pilih Tahun -</option>
                                        @foreach (range(date('Y'), 2020) as $year)
                                            <option value="{{ $year }}"
                                                {{ request('year_external') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="program_external" class="form-select">
                                        <option value="">- Pilih Program -</option>
                                        @foreach ($programs as $program)
                                            <option value="{{ $program->id }}"
                                                {{ request('program_external') == $program->id ? 'selected' : '' }}>
                                                {{ $program->program_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex gap-2">
                                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Reset</a>
                                </div>
                            </form>
                        </div>

                        {{-- Tombol Create --}}
                        <div class="col-lg-2 col-md-3 text-end">
                            <a href="{{ route('external-letters.create') }}" class="btn btn-primary w-100">Create</a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped align-middle">
                            <thead class="table-light text-nowrap">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nomor Memo</th>
                                    <th style="width: 35%">Perihal</th>
                                    <th>Penerima</th>
                                    <th>PIC</th>
                                    <th colspan="2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($externalLetters as $external)
                                    <tr @if (!$external->letter_document) class="table-warning" @endif>
                                        <td>{{ \Carbon\Carbon::parse($external->letter_date)->format('d M Y') }}</td>
                                        <td>{{ $external->letter_no }}</td>
                                        <td>{{ $external->subject }}</td>
                                        <td>{{ $external->recipient_initial }}</td>
                                        <td>
                                            {{ $external->user ? ucwords(str_replace('.', ' ', Str::before($external->user->email, '@'))) : 'Unknown' }}
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                @if ($external->letter_document)
                                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                        data-bs-target="#docModalExternal{{ $external->id }}">
                                                        View
                                                    </button>
                                                @else
                                                    <span class="badge bg-secondary">No Doc</span>
                                                @endif
                                                <a href="{{ route('external-letters.edit', $external->id) }}"
                                                    class="btn btn-sm btn-warning">Edit</a>
                                                @if (!$external->letter_document)
                                                    <form action="{{ route('external-letters.destroy', $external->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Yakin ingin menghapus nomor memo ini?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4"><em>Tidak ada memo
                                                eksternal.</em></td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Internal --}}
        @foreach ($internalLetters as $internal)
            @if ($internal->letter_document)
                <div class="modal fade" id="docModalInternal{{ $internal->id }}" tabindex="-1"
                    aria-labelledby="docModalLabelInternal{{ $internal->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Dokumen: {{ $internal->letter_no }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <iframe src="{{ asset('storage/' . $internal->letter_document) }}" width="100%"
                                    height="600px" style="border:none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        {{-- Modal External --}}
        @foreach ($externalLetters as $external)
            @if ($external->letter_document)
                <div class="modal fade" id="docModalExternal{{ $external->id }}" tabindex="-1"
                    aria-labelledby="docModalLabelExternal{{ $external->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Dokumen: {{ $external->letter_no }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <iframe src="{{ asset('storage/' . $external->letter_document) }}" width="100%"
                                    height="600px" style="border:none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endsection

    {{-- Tab Persistence --}}
    @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const lastTab = localStorage.getItem('lastActiveTab');
                if (lastTab) {
                    const tabTrigger = document.querySelector(`button[data-bs-target="${lastTab}"]`);
                    if (tabTrigger) {
                        new bootstrap.Tab(tabTrigger).show();
                    }
                }
                document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(btn => {
                    btn.addEventListener('shown.bs.tab', function(e) {
                        const target = e.target.getAttribute('data-bs-target');
                        localStorage.setItem('lastActiveTab', target);
                    });
                });
            });
        </script>
    @endsection
@endhasanyrole
