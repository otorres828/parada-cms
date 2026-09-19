@props(['classe'=>false])
<button type="button" class="{{ !$classe ? 'btn btn-outline-secondary' :'d-none d-md-inline-block btn btn-light me-1' }}" {{ $attributes }} >
    {{ $classe ? 'Autorizar' : '' }}
   <i class="bi bi-shield-lock"></i>
</button>
