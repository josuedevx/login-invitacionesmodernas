# 🧩 Invitaciones Modernas — Módulo de Inicio de Sesión

Sistema de autenticación moderna con integración OAuth (Google), manejo de recuperación de contraseñas, validación de tokens y estructura modular escalable en PHP.

---

## 📁 Estructura del Proyecto

    invitaciones-modernas
      ├── auth
      │   ├── config
      │   │   └── DBConection.php
      │   ├── controllers
      │   │   ├── AccountController.php
      │   │   ├── AuthRecoveryController.php
      │   │   └── AuthController.php
      │   ├── middleware
      │   │   ├── RequestURI.php
      │   │   └── FBLogin.php
      │   ├── model
      │   │   ├── AccountModel.php
      │   │   ├── AuthRecoveryModel.php
      │   │   └── AuthModel.php
      │   ├── FBRedirect.php
      │   ├── OAuthHandler.php
      │   ├── Redirect.php
      │   └── SignOut.php
      ├── css
      │   └── styles.css
      ├── js
      │   ├── auth.js
      │   └── functions.js
      ├── vendor
      ├── .env
      ├── composer.json
      ├── composer.lock
      ├── home.php
      └── index.php

---

**📘 Descripción general:**

Este módulo forma parte del sistema **Invitaciones Modernas**, permitiendo un flujo seguro de autenticación con soporte para:

- 🔐 Inicio de sesión tradicional con correo y contraseña.
- 🌐 Autenticación OAuth (Google y Facebook).
- 🔁 Recuperación y restablecimiento de contraseñas.
- 🧱 Arquitectura modular (config / controller / model).
- ⚡ Variables de entorno y configuración mediante `.env`.

---

## ⚙️ Configuración

### 1️⃣ Instalar dependencias

Asegúrate de tener [Composer](https://getcomposer.org) instalado y luego ejecuta:

```bash
composer install
```

### 2️⃣ Configurar archivo .env

Crear un archivo `.env` en la raíz del proyecto con los siguientes valores:

```env
# === GOOGLE AUTH ===
GOOGLE_API_KEY=tu_api_key
GOOGLE_CLIENT_ID=tu_cliente_id
GOOGLE_CLIENT_SECRET=tu_cliente_secret

# === DATABASE ===
DATABASE_DB_NAME=nombre_db
DATABASE_DB_HOST=localhost
DATABASE_DB_USER=root
DATABASE_DB_PASSWORD=

# === SECRETS ===
JWT_SECRET_KEY=clave_secreta_segura

# === EMAIL SERVICE ===
MAIL_HOST = smtp.gmail.com
MAIL_PORT = 587
MAIL_USERNAME = youremail@example.com
MAIL_PASSWORD=tu_password
MAIL_FROM = youremail@example.com
SMTP_FROM_NAME = "Invitaciones Modernas"

# === HOST FB APIKEYS ===
FACEBOOK_APP_ID = app_id
FACEBOOK_APP_SECRET = app_secret

# === HOST DE REDIRECCIÓN ===
HOST_URL=home.php
BASE_URL=http://localhost:8000 o https://tusitioweb
```

### 3️⃣ Ejecutar servidor local

Para iniciar el flujo ejecutar en la terminal:

```bash
php -S localhost:8000
```

Esto levanta un servidor local en `http://localhost:8000`.

---

## 🔐 Autenticación con Google OAuth 2.0

### 1️⃣ Crear credenciales en Google Cloud Console

1. Accede a [https://console.cloud.google.com/apis/credentials](https://console.cloud.google.com/apis/credentials)
2. Crea un nuevo proyecto o usa uno existente.
3. Habilita la **Google Identity API (OAuth)**.
4. Crea un **ID de cliente OAuth 2.0** con tipo **Aplicación web**.
5. Agrega como **URI de redirección autorizado (Authorized redirect URIs)**:

   ```
   http://localhost:8000/auth/Redirect.php
   https://tusitioweb.com/auth/Redirect.php
   ```

6. Copia tu `CLIENT_ID`, `CLIENT_SECRET` y `GOOGLE_REDIRECT_URI` al archivo `.env`.
7. Crea un **API Keys** y da click en **Show Key**.
8. Copia el valor del API Keys en `GOOGLE_API_KEY` al archivo `.env`.

### 2️⃣ Flujo resumido

- El usuario hace clic en "Iniciar sesión con Google".
- Se genera la URL OAuth y se redirige a Google.
- Google devuelve un `code` al endpoint (`Redirect.php`).
- Se intercambia el `code` por un `access_token`.
- Se obtiene la información del usuario (correo, nombre) y se registra o autentica.

---

## 🌐 Autenticación con Facebook (Meta Developers)

### 1️⃣ Crear app en Meta Developers

1. Ingresa a [https://developers.facebook.com/apps](https://developers.facebook.com/apps)
2. Inicia sesión o registra una cuenta como developer.
3. Crea una **nueva app**.
4. Añade el caso de uso **Autenticar y solicitar datos a usuarios con el inicio de sesión con Facebook** → “Web”.
5. Configura la aplicación añadiendola a un portafolio o crea uno nuevo.
6. En la configuración de **Personalizar** casos de uso en **URL de redirección de OAuth válidos**, agrega:

   ```
   https://tusitioweb.com/auth/FBRedirect.php
   ```

7. Copia el **App ID** y **App Secret** al archivo `.env`.

### 2️⃣ Flujo resumido

- `FBLogin.php` construye la URL de autenticación con `scope=email,public_profile`
- El usuario autoriza y es redirigido a `FBRedirect.php` con un `code`
- Tu app intercambia el `code` por un `access_token` en la API Graph
- Luego obtiene la información del usuario con `https://graph.facebook.com/me?fields=id,name,email`
- Se guarda o autentica al usuario en el sistema

---

## 📚 Archivos Clave

| Archivo                       | Descripción                                         |
| ----------------------------- | --------------------------------------------------- |
| `auth/middleware/FBLogin.php` | Redirección inicial al login de Facebook            |
| `auth/FBRedirect.php`         | Procesa el código y obtiene datos del usuario       |
| `auth/Redirect.php`           | Redirección de Google OAuth                         |
| `auth/OAuthHandler.php`       | Lógica de registro/autenticación                    |
| `.env`                        | Variables de entorno (credenciales y configuración) |

---

## 🚀 Uso del Sistema

- Accede al panel de inicio de sesión.
- Prueba tanto el login manual como el OAuth 2.0 y Facebook
- En caso de olvidar la contraseña, utiliza el flujo de Password Reset.

---

# 🧮 Base de Datos

Esta documentación describe las tablas, campos, tipos de datos y relaciones de la base de datos del sistema.

---

## ÍNDICE

1. [Tabla `users`](#tabla-users)
2. [Tabla `password_resets`](#tabla-password_resets)

---

## Tabla `users`

Entidad principal que representa a cada usuario del sistema.

| Campo      | Tipo         | Atributos                   | Descripción                     |
| ---------- | ------------ | --------------------------- | ------------------------------- |
| `id`       | INT(11)      | PRIMARY KEY, AUTO_INCREMENT | Identificador único del usuario |
| `email`    | VARCHAR(25)  | NOT NULL, UNIQUE            | Correo electrónico para login   |
| `password` | VARCHAR(255) | NOT NULL                    | Hash de la contraseña           |
| `token`    | VARCHAR(255) | NULL                        | Token de acceso                 |
| `status`   | INT(11)      | NOT NULL, DEFAULT 1         | Estado (1=activo, 0=inactivo)   |

---

## Tabla `password_resets`

Información personal asociada a cada usuario.

| Campo         | Tipo         | Atributos                     | Descripción                   |
| ------------- | ------------ | ----------------------------- | ----------------------------- |
| `id`          | INT(11)      | PRIMARY KEY, AUTO_INCREMENT   | Identificador único           |
| `user_id`     | INT(11)      | NOT NULL, FOREIGN KEY → users | Usuario al que pertenece      |
| `email`       | VARCHAR(255) | NOT NULL                      | Correo al que pertenece       |
| `reset_code`  | VARCHAR(6)   | NOT NULL                      | Codigo de 6 digitos a validar |
| `reset_token` | VARCHAR(255) | NOT NULL                      | Token de validaciión          |
| `expires_at`  | datetime     | NOT NULL                      | Tiempo de expiración del code |
| `used`        | tinyint(1)   | NULL, DEFAULT 0               | 0 no usado / 1 ya usado       |
| `created_at`  | timestamp    | NOT NULL                      | Fecha de creación             |

---

## 🔗 Relaciones entre tablas

- **users**  
  1 → (`users.id`) → password_resets (`password_resets.user_id`)

---

## 💾 Ejemplos de Inserción

A continuación se muestran ejemplos de cómo insertar datos en las tablas para un flujo típico de registro y uso del sistema.

### 1. Insertar un usuario

```sql
INSERT INTO `users` (email, password, token, status)
VALUES ('jonhdoe@example.com', '$2y$10$hashedPassword123...', 'eyJ0eXAiOiJKV1QiLCJ...', 'token123abc', 1);
```

### 2. Insertar un codigo de accesso

```sql
INSERT INTO `password_resets` (user_id, email, reset_code, reset_token, expires_at)
VALUES (1, 'jonhdoe@example.com', '123456', 'hjksdhisdui...', '2025-11-04 18:42:04');
```

---

## 🧠 Clase de Conexión a Base de Datos

El siguiente fragmento muestra la clase `DBConection` que gestiona la conexión a la base de datos:

```php
<?php
class DBConection
{
    public static function connect()
    {
        $DATABASE_HOST = '';
        $DATABASE_USER = '';
        $DATABASE_PASS = '';
        $DATABASE_NAME = '';

        $conexion = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

        if (!$conexion) {
            throw new Exception('Fallo en la conexión de MySQL: ' . mysqli_connect_error());
        }

        return $conexion;
    }
}
?>
```

---

# 🔐 Consumo del Servicio `fetch_user`

Esta guía explica cómo **consumir el endpoint externo `fetch_user`** del sistema de autenticación de **Invitaciones Modernas**.  
Su función es **crear un usuario automáticamente** a partir de un correo electrónico y devolver una **contraseña temporal autogenerada** junto con un token de sesión (JWT).

---

## 🌐 Endpoint

| Entorno    | URL                                                                  |
| ---------- | -------------------------------------------------------------------- |
| Local      | `http://localhost:8000/auth/controllers/AccountController.php`       |
| Producción | `https://test.dervianseo.com/auth/v1/register` |

---

## 📩 Método y Acción

El servicio solo acepta **solicitudes POST**, y es **obligatorio enviar la acción `fetch_user`** para que el backend identifique correctamente la operación.

### 🔸 Parámetros requeridos

| Key      | Valor                 | Descripción                                                |
| -------- | --------------------- | ---------------------------------------------------------- |
| `action` | `fetch_user`          | Indica al API que debe ejecutar la función correspondiente |
| `email`  | `cliente@ejemplo.com` | Correo del usuario a registrar                             |

---

## ⚙️ Ejemplo 1: Uso con JavaScript (fetch API)

```js
const formData = new FormData();
formData.append("action", "fetch_user");
formData.append("email", "cliente@ejemplo.com");

fetch("https://test.dervianseo.com/auth/v1/register", {
  method: "POST",
  body: formData,
})
  .then((res) => res.json())
  .then((data) => {
    if (data.success) {
      console.log("✅ Usuario creado:", data.email);
      console.log("🔑 Contraseña temporal:", data.temporal_password);
      console.log("🪪 Token JWT:", data.token);
    } else {
      console.error("❌ Error:", data.message);
    }
  })
  .catch((err) => console.error("🚫 Error en la solicitud:", err));
```

---

## ⚙️ Ejemplo 2: Uso con PHP (cURL)

```php
$postData = [
    "action" => "fetch_user",
    "email" => "cliente@ejemplo.com"
];

$ch = curl_init("https://test.dervianseo.com/auth/v1/register");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
```

---

## ⚙️ Ejemplo 3: Uso con Python (requests)

```python
import requests

url = "https://test.dervianseo.com/auth/v1/register"
data = {
    "action": "fetch_user",
    "email": "cliente@ejemplo.com"
}

response = requests.post(url, data=data)
print(response.json())
```

---

## ⚙️ Ejemplo 4: Uso con Postman

1. Selecciona método **POST**
2. En la pestaña **Body → form-data**, agrega:

| Key    | Value               |
| ------ | ------------------- |
| action | fetch_user          |
| email  | cliente@ejemplo.com |

3. Envía la solicitud.
4. El sistema devolverá un JSON con los datos del usuario generado.

---

## 📤 Respuesta Exitosa (JSON)

```json
{
  "success": true,
  "email": "cliente@ejemplo.com",
  "temporal_password": "674a812baf1c5",
  "token": "JWT_TOKEN_GENERADO",
  "message": "¡Registro completado exitosamente!",
  "code": 200
}
```

---

## ⚠️ Códigos de Error y Mensajes

| Código | Mensaje                                                         | Causa                                            |
| ------ | --------------------------------------------------------------- | ------------------------------------------------ |
| 200    | `¡Registro completado exitosamente!`                            | Operación completada con éxito                   |
| 400    | `Email inválido`                                                | El formato del correo no es válido               |
| 400    | `Este email ya está asociado a una cuenta existente.`           | El usuario ya existe en la base de datos         |
| 400    | `Nuestra plataforma ha alcanzado su capacidad máxima.`          | Se llegó al límite de usuarios permitidos        |
| 400    | `Error al crear usuario`                                        | No se pudo registrar el usuario en la base       |
| 500    | `Error desconocido al registrar usuario`                        | Error interno del servidor                       |
| 500    | `Error al procesar el registro. Por favor, intenta nuevamente.` | Falla en la inserción en base de datos           |
| 405    | `Método no permitido`                                           | El endpoint no recibió una solicitud POST válida |

---

## 🧠 Detalles Técnicos

- **Password temporal**: Se crea automáticamente con `uniqid()` en PHP.
- **Sesión**: Se inicia automáticamente al crear el usuario.

---

## 🔄 Flujo General del Servicio `fetch_user`

1. Se envía una solicitud **POST** con `action=fetch_user` y un correo electrónico.
2. El backend valida el correo y genera una contraseña temporal.
3. El usuario se crea en la base de datos junto con su rol.
4. Se genera un token JWT y se devuelve junto con los datos del usuario.
5. El sistema cliente puede usar esta información para enviar un correo con las credenciales.

---

© 2025 - Documentación técnica del módulo **AccountController - fetch_user()**

---

## 👨‍💻 Créditos

Desarrollado por [Josué](https://github.com/josuedevx).

---

## 📄 Licencia

Este proyecto es propiedad privada de Link Socially.
No está autorizado su uso, distribución o modificación sin consentimiento explícito.
Consulta el archivo `LICENSE` para más detalles

---
