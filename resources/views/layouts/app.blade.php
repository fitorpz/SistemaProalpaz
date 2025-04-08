<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('logo.png') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <title>PROALPAZ</title>

    <style>
        body,
        html {
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 100%;
            padding: 0 15px;
            margin: 0 auto;
        }

        .card {
            word-wrap: break-word;
            max-width: 100%;
        }

        /* Header clásico (login o no autenticado) */
        .header-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .logo-container {
            text-align: center;
        }

        .logo-img {
            max-width: 300px;
            height: auto;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
            border-radius: 10px;
        }

        .logo-img:hover {
            transform: scale(1.1);
            box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.5);
        }

        .user-info {
            text-align: left;
            max-width: 300px;
            max-height: 100px;
        }

        .alert {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 10px;
            margin: 0;
        }

        .btn-logout {
            white-space: nowrap;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                text-align: center;
            }

            .user-info {
                text-align: center;
                max-width: 100%;
            }

            .logo-img {
                max-width: 250px;
            }

            .alert {
                align-items: center;
            }

            .header-mini {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }

            .header-mini .logo img {
                height: 40px;
                /* reducir logo en responsive */
            }

            .header-mini .user-actions {
                flex-direction: row;
                align-items: center;
                gap: 8px;
            }

        }

        /* Header compacto (dentro del sistema) */
        .header-mini {
            background-color: rgb(255, 255, 255);
            color: #fff;
            padding: 0.2rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        }

        .header-mini .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-mini .logo img {
            height: 80px;
            transition: transform 0.3s ease;
        }

        .header-mini .logo img:hover {
            transform: scale(1.1);
        }

        .header-mini .user-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .header-mini .user-actions i {
            color: rgb(69, 2, 255);
        }

        .header-mini .logout-btn {
            color: rgb(0, 0, 0);
            border: 1px solidrgb(0, 0, 0);
            padding: 5px 10px;
            background: transparent;
            border-radius: 5px;
        }

        @media (max-width: 576px) {
            .header-mini {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .header-mini .user-actions {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>

    {{-- CABECERA DINÁMICA --}}
    @if (Request::is('login') || !auth()->check())
    <div class="container mt-4">
        <div class="header-container my-4">
            <div class="logo-container">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ asset('logoHeader.png') }}" alt="Logo" class="logo-img">
                </a>
            </div>

            @auth
            <div class="user-info">
                <div class="alert alert-info">
                    <strong>Bienvenido, {{ auth()->user()->nombre ?? auth()->user()->name }}!</strong><br>
                    <span>Correo: {{ auth()->user()->email }}</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm btn-logout">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
            <div class="mb-3"><br>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Atrás
                </a>
            </div>
            @endauth
        </div>
    </div>
    @else
    <div class="header-mini">
        <div class="logo">
            <a href="{{ route('dashboard') }}">
                <img src="{{ asset('logoHeader.png') }}" alt="Logo">
            </a>
        </div>

        <div class="user-actions">
            <div style="color:rgb(0, 0, 0);"><i class="fas fa-user"></i> {{ auth()->user()->nombre ?? auth()->user()->name }}</div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <!-- Botón de descarga sin subrayado -->
                <a href="{{ asset('apk/ProalpazApp.apk') }}" class="btn btn-outline-primary" download style="text-decoration: none;">
                    <i class="fas fa-download"></i> Descargar App
                </a>



                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- CONTENIDO PRINCIPAL --}}
    <div class="container-fluid mt-4">
        @yield('content')
    </div>

    <!-- 🔥 Cargar scripts en el orden correcto -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <!-- Verificación de carga -->
    <script>
        $(document).ready(function() {
            console.log("✅ jQuery:", typeof jQuery !== "undefined");
            console.log("✅ Select2:", $.fn.select2 ? "Sí" : "No");
            console.log("✅ jQuery UI:", $.fn.autocomplete ? "Sí" : "No");
        });
    </script>

    <!-- Scripts personalizados -->
    @yield('scripts')
    @stack('scripts')
    {{-- FOOTER --}}
    <footer class="bg-light text-center text-muted py-3 mt-5 border-top">
        <div class="container">
            <small>
                &copy; {{ date('Y') }} IDB Todos los derechos reservados.
                |
                <a href="#" class="text-decoration-none text-muted">Términos</a> ·
                <a href="#" class="text-decoration-none text-muted">Privacidad</a>
            </small>
        </div>
    </footer>

</body>

</html>