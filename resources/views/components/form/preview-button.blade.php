@props(['link'])
<a href="{{ $link ?? '#'}}" class="btn btn-secondary me-2" target="_blanck">
  {{ $slot }}
</a>
