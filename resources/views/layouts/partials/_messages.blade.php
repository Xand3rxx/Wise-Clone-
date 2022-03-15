
{{-- Success Alert --}}
@if($message = Session::get('success'))
  @push('scripts')
    <script>
      var message = '{{ $message }}';
      var type = 'success';
      displayMessage(message, type);
    </script>
  @endpush
@endif

{{-- Warning Alert --}}
@if(Session::has('warning'))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
  <strong>Something went wrong!</strong> {{ Session::get('warning') }}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span>&times;</span>
  </button>
</div>
@endif

{{-- If the page has any error passed to it --}}
@if ($errors->any())
  @foreach ($errors->all() as $error)
    @push('scripts')
      <script>
        var message = '{{ $error }}';
        var type = 'error';
        displayMessage(message, type);
      </script>
    @endpush
  @endforeach
@endif

@if ($message = Session::get('error'))
  @push('scripts')
    <script>
      var message = '{{ $message }}';
      var type = 'error';
      displayMessage(message, type);
    </script>
  @endpush
@endif
