<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Inicio - Invitaciones Modernas</title>
    <link rel="icon" href="https://invitacionesmodernas.b-cdn.net/assets/img/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.2/css/all.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.10.2/mdb.min.css">
    <link rel="stylesheet" href="css/home.css?v=<?= time() ?>" />
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const access_token = urlParams.get("authorize");

        if (access_token) {
            localStorage.setItem("access_token", access_token);
        }

    </script>
</head>

<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay">
        <div class="spinner"></div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="https://invitacionesmodernas.b-cdn.net/assets/img/logo.png" height="40" alt="Logo"
                    class="me-2">
                <span class="fw-bold d-none">Invitaciones Modernas</span>
            </a>

            <button class="navbar-toggler" type="button" data-mdb-toggle="collapse" data-mdb-target="#navbarNav">
                <i class="fas fa-bars"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <!-- <a class="nav-link active" href="#">Inicio</a> -->
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark d-flex align-items-center" href="#"
                            id="navbarDropdown" role="button" data-mdb-toggle="dropdown">
                            <div class="user-avatar me-2 ">
                                <?php
                                $email = $_SESSION['email'];
                                echo strtoupper(substr($email, 0, 1));
                                ?>
                            </div>
                            <span><?php echo explode('@', $_SESSION['email'])[0]; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Mi Perfil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="auth/SignOut.php" onclick="SignOut()">Cerrar
                                    Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">¡Bienvenido a tu espacio creativo!</h1>
                    <p class="lead mb-4">Crea invitaciones únicas y memorables para tus momentos especiales.
                        Personaliza, compra y comparte con facilidad.</p>
                    <div class="d-flex flex-wrap gap-3 d-none">
                        <a href="#" class="btn btn-light btn-lg btn-custom">Explorar Catálogo</a>
                        <a href="#" class="btn btn-outline-light btn-lg">Crear Mi Diseño</a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="https://invitacionesmodernas.b-cdn.net/assets/img/logo.png" alt="Invitaciones"
                        class="img-fluid" style="max-height: 400px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Welcome Card -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="welcome-card p-5">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="fw-bold text-primary mb-3">¡Hola,
                                    <?php echo explode('@', $_SESSION['email'])[0]; ?>!
                                </h3>
                                <p class="text-muted mb-4">Es un placer tenerte de vuelta. ¿Listo para crear algo
                                    increíble hoy?</p>
                                <div class="d-flex flex-wrap gap-3 align-items-center d-none">
                                    <span class="stats-badge">2 diseños en progreso</span>
                                    <span class="stats-badge"
                                        style="background: linear-gradient(45deg, #A1C4FD, #C2E9FB);">1 pedido
                                        activo</span>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="feature-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <a href="#" class="btn btn-primary mt-3  d-none">Continuar Mis Diseños</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">¿Qué te gustaría hacer hoy?</h2>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="feature-card p-4 text-center">
                        <div class="feature-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h4>Explorar Catálogo</h4>
                        <p class="text-muted">Descubre cientos de diseños profesionales para cualquier ocasión.</p>
                        <a href="https://invitacionesmodernas.com/productos" target="_blank"
                            class="btn btn-outline-primary">Comenzar</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="feature-card p-4 text-center">
                        <div class="feature-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h4>Ver colecciones</h4>
                        <p class="text-muted">Conoce nuestras colecciones de XV.</p>
                        <a href="https://invitacionesmodernas.com/quinceanera/xv-colecciones"
                            class="btn btn-outline-primary" target="_blank">Ver</a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">¿Tienes una idea en mente?</h2>
            <p class="lead mb-5">Nuestro equipo de diseñadores puede crear la invitación perfecta para tu evento
                especial.</p>
            <a href="https://invitacionesmodernas.com/contacto" target="_blank"
                class="btn btn-light btn-lg btn-custom">Solicitar Diseño Personalizado</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer py-4 d-none">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <img src="https://invitacionesmodernas.b-cdn.net/assets/img/logo.png" height="40" alt="Logo"
                        class="mb-3">
                    <p>Creando momentos inolvidables a través de diseños excepcionales.</p>
                </div>
                <div class="col-lg-2 col-6 mb-4">
                    <h5>Enlaces</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50 text-decoration-none">Inicio</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Catálogo</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Nosotros</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Contacto</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-6 mb-4">
                    <h5>Soporte</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50 text-decoration-none">Ayuda</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">FAQ</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Términos</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Privacidad</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5>Contacto</h5>
                    <p class="text-white-50">
                        <i class="fas fa-envelope me-2"></i> hola@invitacionesmodernas.com<br>
                        <i class="fas fa-phone me-2"></i> +1 (555) 123-4567
                    </p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="text-white-50 mb-0">&copy; 2024 Invitaciones Modernas. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.10.2/mdb.min.js"></script>
    <script type="text/javascript" src="js/functions.js?v=<?= time() ?>"></script>

    <script>
        // Hide loading overlay when page is loaded
        window.addEventListener('load', function () {
            document.querySelector('.loading-overlay').style.display = 'none';
        });
    </script>
</body>

</html>