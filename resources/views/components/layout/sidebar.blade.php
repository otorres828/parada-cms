<aside
  class="app-sidebar shadow"
  data-bs-theme="dark"
  onclick="event.stopPropagation();"
>
  <x-layout.sidebar.logo />

  <div class="sidebar-wrapper">
    <nav class="mt-2">
      <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main navigation" data-accordion="false" id="navigation">
        <x-layout.sidebar.administration-menu-admin/>
      </ul>
    </nav>
  </div>
</aside>
