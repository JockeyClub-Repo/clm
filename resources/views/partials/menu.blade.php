<div class="app-menu navbar-menu">
  <div class="navbar-brand-box">
    <a href="{{ route('dashboard') }}" class="logo logo-light">
      <span class="logo-sm">
        <img src="{{ asset('assets/images/logo-sm.png') }}" height="50">
      </span>
      <span class="logo-lg">
        <img src="{{ asset('assets/images/logo-light-1.png') }}" height="50">
      </span>
    </a>
    <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
      <i class="ri-record-circle-line"></i>
    </button>
  </div>
    <div id="scrollbar">
      <div class="container-fluid">
        <div id="two-column-menu"></div>
          <ul class="navbar-nav" id="navbar-nav">
            <li class="menu-title"><span>Dashboard</span></li>
            <li class="nav-item">
              <a class="nav-link menu-link" href="{{ route('dashboard') }}">
                <i class="ri-slideshow-fill"></i> <span>Dashboard</span>
              </a>
            </li>
            <li class="menu-title"><span>Gestion</span></li>
            {{-- Solo para AGENT --}}
            @if(auth()->user()->role === 'agent')
              <!--<li class="nav-item">
                <a class="nav-link menu-link" href="#">
                  <i class="ri-money-dollar-circle-line"></i> <span>Nuevo Ticket</span>
                </a>
              </li>-->
            @endif
            {{-- Solo para ADMIN --}}
              @if(auth()->user()->role === 'admin')
                <li class="menu-title"><span>Mantenimiento</span></li>
                <li class="nav-item">
                  <a class="nav-link menu-link" href="{{ route('contracts.index') }}">
                    <i class="ri-folder-5-fill"></i> <span>Contratos</span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link menu-link" href="{{ route('users.index') }}">
                    <i class="ri-user-line"></i> <span>Usuario</span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link menu-link" href="{{ route('providers.index') }}">
                    <i class="ri-money-dollar-circle-line"></i> <span>Proveedores</span>
                  </a>
                </li>
                @endif

            </ul>
        </div>
    </div>


    <div class="sidebar-background"></div>
</div>
