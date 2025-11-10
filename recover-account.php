
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Recuperar Mi Cuenta - Invitaciones Modernas</title>
    <link rel="icon" href="https://invitacionesmodernas.b-cdn.net/assets/img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.2/css/all.css" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login-form.css?v=<?= time() ?>" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .recovery-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .recovery-step {
            display: none;
        }

        .recovery-step.active {
            display: block;
        }

        .btn-recovery {
            background: linear-gradient(135deg, #E37C7D 0%, #F8B1B1 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="loading-overlay">
        <div class="spinner"></div>
    </div>


    <div class="container">
        <div class="recovery-container">
            <div class="text-center mb-4">
                <img src="https://invitacionesmodernas.b-cdn.net/assets/img/logo.png" alt="Logo" width="150">
                <h2 class="mt-3">🔒 Recuperar Mi Cuenta</h2>
                <p class="text-muted">Sigue estos pasos para recuperar el acceso a tu cuenta</p>
            </div>

            <!-- Paso 1: Ingresar Email -->
            <div id="step1" class="recovery-step active">
                <h4>Paso 1: Verificar Email</h4>
                <p>Ingresa el email de tu cuenta para iniciar el proceso de recuperación.</p>
                <form id="initiateRecoveryForm">
                    <div class="mb-3">
                        <input type="email" class="form-control" placeholder="tu@email.com" required>
                    </div>
                    <button type="submit" class="btn btn-recovery w-100">Enviar Código de Verificación</button>
                </form>
            </div>

            <!-- Paso 2: Verificar Código -->
            <div id="step2" class="recovery-step">
                <h4>Paso 2: Verificar Identidad</h4>
                <p>Hemos enviado un código de 6 dígitos a tu email. Ingrésalo aquí:</p>
                <form id="verifyCodeForm">
                    <div class="mb-3">
                        <input type="text" class="form-control text-center" placeholder="000000" maxlength="6" required>
                    </div>
                    <button type="submit" class="btn btn-recovery w-100">Verificar Código</button>
                </form>
            </div>

            <!-- Paso 3: Nueva Contraseña -->
            <div id="step3" class="recovery-step">
                <h4>Paso 3: Nueva Contraseña</h4>
                <p>Crea una nueva contraseña segura para tu cuenta.</p>
                <form id="newPasswordForm">
                    <div class="mb-3">
                        <input type="password" class="form-control" placeholder="Nueva contraseña" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" placeholder="Confirmar contraseña" required>
                    </div>
                    <button type="submit" class="btn btn-recovery w-100">Recuperar Cuenta</button>
                </form>
            </div>

            <div id="resultMessage" class="mt-3"></div>
        </div>
    </div>


    <script type="text/javascript" src="js/mdb.min.js?v=<?= time() ?>"></script>
    <script type="text/javascript" src="js/recover.js?v=<?= time() ?>"></script>
</body>

</html>