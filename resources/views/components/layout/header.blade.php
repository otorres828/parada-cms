<nav class="app-header navbar navbar-expand bg-body">

  <div class="container-fluid">

    <ul class="navbar-nav">

      <li class="nav-item">

        <x-layout.header.button-hamburger/>

      </li>

    </ul>


    <ul class="navbar-nav ms-auto">

      {{-- 
      
      <li class="nav-item dropdown">

        <x-layout.header.button-notifications/>

      </li>  
    --}}


      <li class="nav-item user-menu">
        
        <x-layout.header.user/>

      </li>
   
    </ul>
   
  </div>

</nav>