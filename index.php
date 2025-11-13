<?php
require_once __DIR__ . "/vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

session_start();

if (isset($_SESSION['loggedin'])) {
    header('Location: /' . $_ENV['HOST_URL']);
    exit;
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['BASE_URL'] . '/auth/Redirect.php');
$client->setAccessType("offline");
$client->setPrompt("select_account consent");
$client->addScope("email");
$client->addScope("profile");
$googleLoginUrl = $client->createAuthUrl();
?>

<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Inicio de sesión - Invitaciones Modernas</title>
    <link rel="icon" href="https://invitacionesmodernas.b-cdn.net/assets/img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.2/css/all.css" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login-form.css?v=<?= time() ?>" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>

    <div class="loading-overlay">
        <div class="spinner"></div>
    </div>

    <section class="h-100 gradient-form" style="background-color: #eee;">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100 ">
                <div class="col-xl-10 ">
                    <div class="card rounded-3 text-black">
                        <div class="row g-0 ">
                            <div class="col-lg-6">
                                <div class="card-body p-md-5 mx-md-4">

                                    <div class="text-center">
                                        <img src="https://invitacionesmodernas.b-cdn.net/assets/img/logo.png"
                                            class="mb-4" style="width: 185px;" alt="logo">
                                        <h4 class="mt-1 mb-5 pb-1 d-none">Invitaciones Modernas</h4>
                                    </div>

                                    <form action="#" id="formLogin">
                                        <p>Por favor, inicie sesión en su cuenta.</p>

                                        <div class="form-outline mb-4">
                                            <input type="email" id="email" name="email" class="form-control"
                                                placeholder="Ej. youraccount@example.com" />
                                            <label class="form-label" for="email">Correo electrónico</label>
                                        </div>

                                        <div class="form-outline mb-4">
                                            <input type="password" id="password" name="password" class="form-control"
                                                placeholder="*****" />
                                            <span
                                                class="password-toggle position-absolute end-0 top-50 translate-middle-y"
                                                id="passwordToggleLogin">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                            <label class="form-label" for="password">Contraseña</label>
                                        </div>

                                        <div class="text-center pt-1 mb-5 pb-1">
                                            <button class="btn btn-primary btn-block fa-lg gradient-custom-2 mb-3"
                                                id="btnLogin" type="button">Iniciar sesión</button>
                                            <a class="text-muted" href="#!">¿Olvidó su contraseña?</a>


                                            <div class="divider d-flex align-items-center my-4">
                                                <p class="text-center fw-bold mx-3 mb-0 text-muted">O inicia con</p>
                                            </div>

                                            <div class="row align-items-center justify-content-center">
                                                <div class="col-4 mb-2 mb-sm-0 ">
                                                    <a class="btn text-dark border d-flex align-items-center justify-content-center rounded-2 py-8"
                                                        href="<?= htmlspecialchars($googleLoginUrl) ?>" role="button">
                                                        <img src="https://cdn-icons-png.flaticon.com/512/720/720255.png"
                                                            alt="Google Logo" class="img-fluid me-2" width="18"
                                                            height="18">
                                                        <span class="flex-shrink-0"></span>
                                                    </a>
                                                </div>
                                                <div class="col-4">
                                                    <a class="btn text-dark border d-flex align-items-center justify-content-center rounded-2 py-8"
                                                        href="#!" role="button" onclick="handleFbLogin()">
                                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6c/Facebook_Logo_2023.png/1200px-Facebook_Logo_2023.png"
                                                            alt="Facebook Logo" class="img-fluid me-2" width="18"
                                                            height="18">
                                                        <span class="flex-shrink-0"></span>
                                                    </a>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="d-flex align-items-center justify-content-center pb-4">
                                            <p class="mb-0 me-2">¿No tienes una cuenta?</p>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#newAccount">Crear nueva</button>
                                        </div>

                                    </form>

                                </div>
                            </div>
                            <!-- <div class="col-lg-6 d-flex align-items-center gradient-custom-2">
                                <div class="text-white px-3 py-4 p-md-5 mx-md-4 animated-text">
                                    <h4 class="mb-4">Invitaciones que enamoran en tu tienda online</h4>
                                    <p class="small mb-0 ">Nuestros diseños son los mejores del mercado. Nuestro equipo
                                        de diseñadores gráficos profesionales siempre mantienen un estilo único y
                                        elegante con las tendencias del momento. Si requieres un diseño que no
                                        encuentres en otro lado, nosotros te lo hacemos. Garantizado.</p>
                                </div>
                            </div> -->

                            <div class="col-lg-6 d-flex align-items-center gradient-custom-2">
                                <div class="text-white px-3 py-4 p-md-5 mx-md-4 animated-text text-center">
                                    <h4 class="mb-4">Servicios que <span class="glow-text">brillan</span></h4>

                                    <div class="flip-cards-container">
                                        <div class="flip-card">
                                            <div class="flip-card-inner">
                                                <div class="flip-card-front">
                                                    <i class="fas fa-paint-brush"></i>
                                                    <span>Diseño</span>
                                                </div>
                                                <div class="flip-card-back">
                                                    <small>Diseños que inspiran y comunican</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flip-card">
                                            <div class="flip-card-inner">
                                                <div class="flip-card-front">
                                                    <i class="fas fa-magic"></i>
                                                    <span>Creativo</span>
                                                </div>
                                                <div class="flip-card-back">
                                                    <small>Ideas innovadoras y únicas</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flip-card">
                                            <div class="flip-card-inner">
                                                <div class="flip-card-front">
                                                    <i class="fas fa-bolt"></i>
                                                    <span>Rápido</span>
                                                </div>
                                                <div class="flip-card-back">
                                                    <small>Entrega en 24 horas</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flip-card">
                                            <div class="flip-card-inner">
                                                <div class="flip-card-front">
                                                    <i class="fas fa-star"></i>
                                                    <span>Calidad</span>
                                                </div>
                                                <div class="flip-card-back">
                                                    <small>Materiales premium</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="small mb-0 mt-3">Experiencia y dedicación en cada proyecto para superar
                                        tus expectativas con un toque mágico.</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Restablecer Contraseña -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Restablecer Contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="steps-container">
                        <div class="step active" data-step="1">
                            <div class="step-number">1</div>
                            <div class="step-title">Verificar Email</div>
                        </div>
                        <div class="step" data-step="2">
                            <div class="step-number">2</div>
                            <div class="step-title">Ingresar Código</div>
                        </div>
                        <div class="step" data-step="3">
                            <div class="step-number">3</div>
                            <div class="step-title">Nueva Contraseña</div>
                        </div>
                    </div>

                    <!-- Paso 1: Ingresar Email -->
                    <div id="step1" class="reset-step">
                        <p>Ingresa tu correo electrónico para recibir un código de verificación.</p>
                        <div class="form-outline mb-4">
                            <input type="email" id="resetEmail" class="form-control" placeholder="tu@email.com" />
                            <label class="form-label" for="resetEmail">Correo electrónico</label>
                        </div>
                    </div>

                    <!-- Paso 2: Ingresar Código -->
                    <div id="step2" class="reset-step" style="display: none;">
                        <p>Hemos enviado un código de 6 dígitos a tu correo.</p>
                        <div class="form-outline mb-4">
                            <input type="text" id="resetCode" class="form-control text-center" placeholder="000000"
                                maxlength="6" />
                            <label class="form-label" for="resetCode">Código de verificación</label>
                        </div>
                        <p class="small text-muted">
                            ¿No recibiste el código?
                            <a href="#" id="resendCode" class="text-primary">Reenviar</a>
                        </p>
                    </div>

                    <!-- Paso 3: Nueva Contraseña -->
                    <div id="step3" class="reset-step" style="display: none;">
                        <p>Ingresa tu nueva contraseña.</p>
                        <div class="form-outline mb-3">
                            <input type="password" id="newPassword" class="form-control" placeholder="******" />
                            <span class="password-toggle position-absolute end-0 top-50 translate-middle-y"
                                id="passwordToggleRes">
                                <i class="fas fa-eye"></i>
                            </span>
                            <label class="form-label" for="newPassword">Nueva contraseña</label>
                        </div>
                        <div class="form-outline mb-4">
                            <input type="password" id="confirmPassword" class="form-control" placeholder="******" />
                            <span class="password-toggle position-absolute end-0 top-50 translate-middle-y"
                                id="passwordToggleResConfirm">
                                <i class="fas fa-eye"></i>
                            </span>
                            <label class="form-label" for="confirmPassword">Confirmar contraseña</label>
                        </div>
                        <div id="passwordError" class="text-danger small" style="display: none;">
                            Las contraseñas no coinciden
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary gradient-custom-2" id="nextStep">Siguiente</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="newAccount" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear cuenta nueva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="formRegister">
                        <div class="form-outline mb-4">
                            <input type="email" name="email" class="form-control"
                                placeholder="Ej. youraccount@example.com" required />
                            <label class="form-label">Correo electrónico</label>
                        </div>

                        <div class="form-outline mb-4">
                            <input type="password" name="password" id="passwordNewAccount" class="form-control"
                                placeholder="*****" minlength="6" required />
                            <span class="password-toggle position-absolute end-0 top-50 translate-middle-y"
                                id="passwordToggleNew">
                                <i class="fas fa-eye"></i>
                            </span>
                            <label class="form-label">Crea tu contraseña</label>
                        </div>

                        <div class="form-outline mb-4">
                            <input type="password" name="confirmPassword" class="form-control" placeholder="*****"
                                required />
                            <span class="password-toggle position-absolute end-0 top-50 translate-middle-y"
                                id="passwordToggleConfirm">
                                <i class="fas fa-eye"></i>
                            </span>
                            <label class="form-label">Confirma tu contraseña</label>
                        </div>

                        <div id="passwordErrorInfo" class="alert alert-danger small" style="display: none;"></div>

                        <div class="text-center pt-1 mb-5 pb-1">
                            <button type="submit" class="btn btn-primary btn-block fa-lg gradient-custom-2 mb-3 w-100">
                                Crear cuenta
                            </button>

                            <a class="text-muted" href="#!" data-bs-dismiss="modal">
                                Ya tengo una cuenta!
                            </a>

                            <div class="divider d-flex align-items-center my-4">
                                <p class="text-center fw-bold mx-3 mb-0 text-muted">O regístrate con</p>
                            </div>

                            <div class="row align-items-center justify-content-center">
                                <div class="col-4 mb-2 mb-sm-0 ">
                                    <a class="btn text-dark border d-flex align-items-center justify-content-center rounded-2 py-8"
                                        href="<?= htmlspecialchars($googleLoginUrl) ?>" role="button">
                                        <img src="https://cdn-icons-png.flaticon.com/512/720/720255.png"
                                            alt="Google Logo" class="img-fluid me-2" width="18" height="18">
                                        <span class="flex-shrink-0">Google</span>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a class="btn text-dark border d-flex align-items-center justify-content-center rounded-2 py-8"
                                        href="#!" role="button" onclick="handleFbLogin()">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6c/Facebook_Logo_2023.png/1200px-Facebook_Logo_2023.png"
                                            alt="Facebook Logo" class="img-fluid me-2" width="18" height="18">
                                        <span class="flex-shrink-0">Facebook</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <svg style="display: none;">
        <symbol id="check-icon" viewBox="0 0 16 16">
            <path
                d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" />
        </symbol>
    </svg>

    <script type="text/javascript" src="js/mdb.min.js?v=<?= time() ?>"></script>
    <script type="text/javascript" src="js/auth.js?v=<?= time() ?>"></script>

</body>

</html>