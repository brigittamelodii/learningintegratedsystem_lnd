<div class="container">
    <h3>Edit Participants for Class: <strong>{{ $class->class_name }}</strong></h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
