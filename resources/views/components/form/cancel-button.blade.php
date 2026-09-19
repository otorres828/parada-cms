@props(['link'])
<a wire:navigate  href="{{ $link ?? '#'}}" class="btn btn-light me-2">
  {{ $slot }}
</a>
