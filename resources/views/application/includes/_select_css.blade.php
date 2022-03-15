@push('styles')
<style>
    .input-group > .select2-container--bootstrap {
    width: 0 !important;
    flex: 1 1 auto !important;
}

.input-group > .select2-container--bootstrap .select2-selection--single {
    height: 100% !important;
    line-height: inherit !important;
    padding: 0.5rem 1rem !important;
}
</style>
@endpush

@push('scripts')
<script>
// $.fn.select2.defaults.set( "theme", "bootstrap" );

$( ".select2" ).select2({
  placeholder: 'Select a Currency',
  width: null
});
</script>
@endpush
