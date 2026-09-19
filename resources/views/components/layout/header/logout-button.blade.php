<form method="POST" action="{{ route('admin.auth.logout') }}">
    @csrf
    <button type="submit" class="btn btn-link" title="Cerrar sesión" aria-label="Cerrar sesión">
        <i class="bi bi-box-arrow-in-right fs-4 orange_icon" aria-hidden="true"></i>
    </button>
</form>
