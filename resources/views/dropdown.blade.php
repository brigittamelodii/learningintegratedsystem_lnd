<div class="modal-body">
    <form>
        {{-- Dropdown Program --}}
        <div class="form-group mb-3">
            <label for="program-dropdown">Pilih Program</label>
            <select id="program-dropdown" class="form-control">
                <option value="">-- Pilih Program --</option>
                @foreach ($programs as $data)
                    <option value="{{ $data->id }}">{{ $data->program_name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Dropdown Class --}}
        <div class="form-group mb-3">
            <label for="class-dropdown">Pilih Kelas</label>
            <select id="class-dropdown" class="form-control">
                <option value="">-- Pilih Kelas --</option>
            </select>
        </div>
    </form>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        $('#program-dropdown').on('change', function() {
            var programID = this.value;
            $("#class-dropdown").html('');
            $.ajax({
                url: "/api/fetch-classes",
                type: "POST",
                data: {
                    program_id: programID,
                },
                dataType: 'json',
                success: function(result) {
                    $('#class-dropdown').html(
                        '<option value="">-- Pilih Kelas --</option>');
                    $.each(result.classes, function(key, value) {
                        $("#class-dropdown").append('<option value="' + value.id +
                            '">' + value.class_name + '</option>');
                    });
                }
            });
        });
    });
</script>
