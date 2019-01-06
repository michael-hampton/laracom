@if ($errors->import->any())
  <div class="alert alert-danger">
    The import has following errors in <strong>line {{ session('error_line') }}</strong>:
      <ul>
        @foreach ($errors->import->all() as $message)
          <li>{{ $message }}</li>
        @endforeach
      </ul>
    </div>
@endif
