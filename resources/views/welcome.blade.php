<!DOCTYPE html>
<html lang="es" data-layout="vertical" data-topbar="light" data-sidebar="light" data-sidebar-size="lg" data-sidebar-image="none">
<head>
  <meta charset="UTF-8">
  <title>Home -CLM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
  <script src="{{ asset('assets/js/layout.js') }}"></script>
  <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet" />
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"rel="stylesheet"/>
</head>
<body>
  <div class="auth-page-wrapper">
    <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
      <div class="bg-overlay"></div>
      <div class="shape">
        <svg viewBox="0 0 1440 120">
          <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
        </svg>
      </div>
    </div>
    <div class="container pb-4">
      <div class="row">
        <div class="col-lg-12 text-center mt-sm-5 text-white">
          <img src="{{ asset('assets/images/logo-light.png') }}" alt="logo" height="90">
          <p class="fs-15 text-white-75 text-white">Sistema CLM - Jockey Club del Perú</p>
          <a href="{{ route('login') }}"class="btn btn-success shadow-sm">Iniciar Sesión</a>
        </div>
      </div>
    </div>
    <div class="container position-relative" style="z-index: 1">
      <div class="row justify-content-center">
        <div class="col-lg-4 card border-0 shadow-sm">
          <div class="card-header text-primary d-flex justify-content-center text-center align-items-center">
            <strong><p class="mb-0">CLM (Contract Lifecycle Management / Gestión del Ciclo de Vida de los Contratos)</p></strong>
          </div>
          <div class="card-body text-center">
            <p class="mb-0">Gestión inteligente de contratos y recordatorios automatizados para asegurar la continuidad de tus operaciones.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
  <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
  <script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
  <script src="{{ asset('assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
  <script src="{{ asset('assets/js/plugins.js') }}"></script>
  <script src="{{ asset('assets/libs/particles.js/particles.js') }}"></script>
  <script src="{{ asset('assets/js/pages/particles.app.js') }}"></script>
</body>
</html>