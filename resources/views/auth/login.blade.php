<!doctype html>
<html lang="es" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none">
<head>
  <meta charset="utf-8" />
  <title>Iniciar Sesión | HelpDesk</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta content="Sistema de Mesa de Ayuda - HelpDesk AnderCode" name="description" />
  <meta content="AnderCode" name="author" />
  <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
  <script src="{{ asset('assets/js/layout.js') }}"></script>
  <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet" />
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
        <div class="col-lg-12 text-center mt-sm-5 text-white-50">
          <img src="{{ asset('assets/images/logo-light.png') }}" alt="logo" height="80">
          <p class="fs-15 text-white-75 text-white">Sistema CLM - Jockey Club del Perú</p>
          <a href="{{ url('/') }}"class="btn btn-success shadow-sm">Inicio</a>
        </div>
      </div>
      <div class="row justify-content-center position-relative" style="z-index: 1">
        <div class="col-md-7 col-lg-5 col-xl-4">
          <div class="card mt-4">
            <div class="card-body p-4">
              <div class="p-2">
                <form method="POST" action="{{ route('login') }}">
                  @csrf
                  <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" name="email" class="form-control form-control" id="email" placeholder="Ingresa tu correo" required autofocus>
                    @error('email')
                      <span class="text-danger">{{ $message }}</span>
                    @enderror
                  </div>
                  <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="position-relative auth-pass-inputgroup mb-3">
                      <input type="password" name="password" class="form-control pe-5" id="password" placeholder="Ingresa tu contraseña" required>
                      <button class="btn btn-link position-absolute end-0 top-0 text-muted" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                    </div>
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                  </div>
                  <div class="mt-4">
                    <button class="btn btn-success w-100" type="submit">Iniciar Sesión</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <footer class="footer text-center">
      <div class="container">
        <p class="text-muted">
          &copy; <script>document.write(new Date().getFullYear())</script> CLM - Jockey Club del Perú.
        </p>
      </div>
    </footer>
  </div>

  <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
  <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
  <script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
  <script src="{{ asset('assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
  <script src="{{ asset('assets/js/plugins.js') }}"></script>
  <script src="{{ asset('assets/libs/particles.js/particles.js') }}"></script>
  <script src="{{ asset('assets/js/pages/particles.app.js') }}"></script>
  <script src="{{ asset('assets/js/pages/password-addon.init.js') }}"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const passwordInput = document.getElementById("password");
      const toggleButton = document.getElementById("password-addon");
      const icon = toggleButton.querySelector("i");

      toggleButton.addEventListener("click", function () {
        if (passwordInput.type === "password") {
          passwordInput.type = "text";
          icon.classList.remove("ri-eye-fill");
          icon.classList.add("ri-eye-off-fill");
        } else {
          passwordInput.type = "password";
          icon.classList.remove("ri-eye-off-fill");
          icon.classList.add("ri-eye-fill");
        }
      });
    });
  </script>

</body>
</html>
