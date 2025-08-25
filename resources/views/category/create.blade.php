@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 style="color: hsl(216, 98%, 52%)"><strong>Create TNA Categories & Training Programs</strong></h3>

        <form action="{{ route('category.store.multiple') }}" method="POST">
            @csrf
            <input type="hidden" name="tna_id" value="{{ $tnaId }}">

            <div id="category-list">
                <!-- Satu kategori -->
                <div class="category-group border p-3 mb-3">
                    <div class="form-group mb-2">
                        <label>Nama Category</label>
                        <input type="text" name="categories[0][name]" class="form-control" required>
                    </div>

                    <div class="tp-list">
                        <h6 class="text-muted">Training Program</h6>
                        <div class="tp-group border p-2 mb-2">
                            <label>Nama Training Program</label>
                            <input type="text" name="categories[0][training_programs][0][tp_name]"
                                class="form-control mb-1" placeholder="Nama Program" required>

                            <label>Durasi (HH:MM)</label>
                            <input type="time" name="categories[0][training_programs][0][tp_duration]"
                                class="form-control mb-1" required>

                            <label>Investment</label>
                            <input type="number" name="categories[0][training_programs][0][tp_invest]"
                                class="form-control mb-1" placeholder="Investasi">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="addTP(this, 0)">+ Tambah
                        Program</button>
                </div>
            </div>

            <div class="form-group">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addCategory()">+ Tambah
                    Kategori</button>
                <button type="submit" class="btn btn-sm btn-success">Simpan Semua</button>
            </div>
        </form>
    </div>

    <script>
        let catIndex = 1;

        function addCategory() {
            const container = document.getElementById('category-list');
            const html = `
        <div class="category-group border p-3 mb-3">
            <div class="form-group mb-2">
                <label>Nama Category</label>
                <input type="text" name="categories[${catIndex}][name]" class="form-control" required>
            </div>

            <div class="tp-list">
                <h6 class="text-muted">Training Program</h6>
                <div class="tp-group border p-2 mb-2">
                    <input type="text" name="categories[${catIndex}][training_programs][0][tp_name]" class="form-control mb-1" placeholder="Nama Program" required>
                    <input type="time" name="categories[${catIndex}][training_programs][0][tp_duration]" class="form-control mb-1" required>
                    <input type="number" name="categories[${catIndex}][training_programs][0][tp_invest]" class="form-control mb-1" placeholder="Investasi">
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-secondary" onclick="addTP(this, ${catIndex})">+ Tambah Program</button>
        </div>
    `;
            container.insertAdjacentHTML('beforeend', html);
            catIndex++;
        }

        function addTP(button, catIdx) {
            const tpList = button.parentElement.querySelector('.tp-list');
            const groupCount = tpList.querySelectorAll('.tp-group').length;

            const html = `
        <div class="tp-group border p-2 mb-2">
            <input type="text" name="categories[${catIdx}][training_programs][${groupCount}][tp_name]" class="form-control mb-1" placeholder="Nama Program" required>
            <input type="time" name="categories[${catIdx}][training_programs][${groupCount}][tp_duration]" class="form-control mb-1" required>
            <input type="number" name="categories[${catIdx}][training_programs][${groupCount}][tp_invest]" class="form-control mb-1" placeholder="Investasi">
        </div>
    `;
            tpList.insertAdjacentHTML('beforeend', html);
        }
    </script>
@endsection
