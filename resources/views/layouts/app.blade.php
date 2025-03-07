<!DOCTYPE html>
<html lang="es">
<head>
    <header>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pamedic</title> <!-- Correct spelling -->
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    </header>
</head>
<body>
<nav class="navbar navbar-expand-lg" style="background-color: #312baa;">
   <div class="container-fluid">
      <a class="navbar-brand" href="{{ url('/') }}"> <img src="{{asset('logos/mini.jpg')}}" width="50" > <span style="color: #f8f9fa">Pamedic</span></a> <!-- Correct spelling -->
      @auth
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div>
        <a class="nav-link" href="{{ url('/') }}"><span style="color: #f8f9fa">{{ Auth::user()->name .' '. Auth::user()->last_name_one }}</span></a>
    </div>
    <div class="collapse navbar-collapse" id="menu">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/attendance') }}"><span style="color: #f8f9fa">Asistencia</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/attendance/list') }}"><span style="color: #f8f9fa">Asignación</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/treatment') }}"><span style="color: #f8f9fa">Tratamiento</span></a>
            </li>
        </ul>
        <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Impresión
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{url('/print') }}">Nota de enfermería</a>
                        <a class="dropdown-item" href="{{url('/print/medic/note') }}">Nota Medica</a>
                    </div>
        </li>
        <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Administración
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ url('/users') }}">Usuarios</a>
                        <a class="dropdown-item" href="{{ url('/edit') }}">Editar Nota de enfermería</a>
                        <a class="dropdown-item" href="{{ url('/patients') }}">Pacientes</a>
                        <a class="dropdown-item" href="{{ url('/medicines') }}">Medicamentos</a>
                    </div>
        </li>
            <!-- <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown03" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Reportes</a>
                <div class="dropdown-menu" aria-labelledby="dropdown03">
                    <a class="dropdown-item" href="#">Reporte de Ventas</a>
                    <a class="dropdown-item" href="#">Reporte por Sucursal</a>
                    <a class="dropdown-item" href="#">Reporte por Turno</a>
                </div> -->
                <li class="nav-item">
                   <a class="nav-link" href="{{ url('/logout') }}">Cerrar Sesión</a>
                </li>
    </div>
    @endauth <!-- Correct Blade directive -->
</div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <main class="container mt-4">
        @yield('content')
    </main>
</body>
<script>
    const $dropdown = $(".dropdown");
    const $dropdownToggle = $(".dropdown-toggle");
    const $dropdownMenu = $(".dropdown-menu");
    const showClass = "show";

    $(window).on("load resize", function() {
        if (this.matchMedia("(min-width: 768px)").matches) {
            $dropdown.hover(
            function() {
                const $this = $(this);
                $this.addClass(showClass);
                $this.find($dropdownToggle).attr("aria-expanded", "true");
                $this.find($dropdownMenu).addClass(showClass);
            },
            function() {
                const $this = $(this);
                $this.removeClass(showClass);
                $this.find($dropdownToggle).attr("aria-expanded", "false");
                $this.find($dropdownMenu).removeClass(showClass);
            }
            );
        } else {
            $dropdown.off("mouseenter mouseleave");
        }
    });
</script>
</html>
