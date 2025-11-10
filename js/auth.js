const formLogin = document.querySelector("#formLogin");
const btnLogin = document.querySelector("#btnLogin");
const loadingOverlay = document.querySelector(".loading-overlay");
const email = document.getElementById("email");
const password = document.getElementById("password");
const redirectSuccess = "/home.php"; // HOST DE REDIRECCIÓN CUANDO SE NECESITA EL DASHBOARD

const handleFbLogin = () => {
  window.location.href = "auth/middleware/FBLogin.php";
};

/* formulario inicio de sesión */

btnLogin.addEventListener("click", handleLogin);

async function handleLogin(event) {
  event.preventDefault();

  if (!email.value || !password.value) {
    alert("Missing password or email");
    return;
  }

  loadingOverlay.style.display = "flex";

  try {
    const form = new FormData(formLogin);
    form.append("action", "request_login");

    const response = await fetch(`auth/controllers/AuthController.php`, {
      method: "POST",
      body: form,
    });

    const json = await response.json();

    if (!json.success) {
      handleLoginError(json);
      return;
    }

    handleLoginSuccess(json);
  } catch (error) {
    console.error("Login error:", await error.text?.());
  } finally {
    loadingOverlay.style.display = "none";
  }
}

/* formulario nueva cuenta */

const formRegister = document.querySelector("#formRegister");

formRegister?.addEventListener("submit", handleRegister);

async function handleRegister(event) {
  event.preventDefault();
  showLoading();

  try {
    const formData = new FormData(event.target);
    formData.append("action", "register_user");

    // Validación cliente
    if (!validateForm(formData)) {
      return;
    }

    const response = await fetch("auth/controllers/AccountController.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      handleRegisterSuccess(result);
    } else {
      handleRegisterError(result);
    }
  } catch (error) {
    console.error("Register error:", error);
    alert("Error de conexión. Intenta nuevamente.");
  } finally {
    hideLoading();
  }
}

function validateForm(formData) {
  const email = formData.get("email");
  const password = formData.get("password");
  const confirmPassword = formData.get("confirmPassword");
  const errorDiv = document.getElementById("passwordErrorInfo");

  // Reset error
  errorDiv.style.display = "none";
  errorDiv.textContent = "";

  // Validaciones
  if (password !== confirmPassword) {
    showError("Las contraseñas no coinciden");
    return false;
  }

  if (password.length < 6) {
    showError("La contraseña debe tener al menos 6 caracteres");
    return false;
  }

  if (!isValidEmail(email)) {
    showError("Por favor ingresa un email válido");
    return false;
  }

  return true;
}

function showError(message) {
  const errorDiv = document.getElementById("passwordErrorInfo");
  errorDiv.textContent = message;
  errorDiv.style.display = "block";
}

function handleRegisterSuccess(result) {
  alert(result.message);
  // Cerrar modal y redirigir
  localStorage.setItem("access_token", result.token);
  window.location.href = redirectSuccess;
  const modal = bootstrap.Modal.getInstance(
    document.getElementById("newAccount")
  );
  modal?.hide();
  formRegister.reset();
}

function handleRegisterError(result) {
  showError(result.message);
}

/* fin nueva cuenta */

function handleLoginSuccess(json) {
  localStorage.setItem("access_token", json.token);
  window.location.href = redirectSuccess;
}

function handleLoginError(json) {
  if (json.code === 500) {
    console.error(
      "⚠️ Error al intentar iniciar sesión: ",
      json.message,
      json.error
    );
  } else {
    alert(json.message);
  }
}

/* formulario reestablecimiento de contraseña */

let currentStep = 1;
let resetData = {
  email: "",
  token: "",
  code: "",
};

let resetPasswordModal;
let newAccountModal;

document.addEventListener("DOMContentLoaded", function () {
  const modalElement = document.getElementById("resetPasswordModal");
  if (modalElement) {
    resetPasswordModal = new bootstrap.Modal(modalElement);
  }

  const modalAccount = document.getElementById("newAccount");
  if (modalAccount) {
    newAccountModal = new bootstrap.Modal(modalAccount);
  }

  setupResetPasswordListeners();
});

function updateStepIndicator(currentStep) {
  const steps = document.querySelectorAll(".step");

  steps.forEach((step) => {
    const stepNumber = parseInt(step.dataset.step);

    // Remover todas las clases
    step.classList.remove("active", "completed");

    // Agregar clases según el paso actual
    if (stepNumber === currentStep) {
      step.classList.add("active");
    } else if (stepNumber < currentStep) {
      step.classList.add("completed");
      // Cambiar el número por el icono de check
      const stepNumberElement = step.querySelector(".step-number");
      stepNumberElement.innerHTML = `<svg class="check-icon" fill="currentColor"><use xlink:href="#check-icon"></use></svg>`;
    }
  });
}

function setupResetPasswordListeners() {
  const forgotPasswordLink = document.querySelector('a[href="#!"]');
  if (forgotPasswordLink) {
    forgotPasswordLink.addEventListener("click", function (e) {
      e.preventDefault();
      if (resetPasswordModal) {
        resetPasswordModal.show();
        resetForm();
      }
    });
  }

  // Manejar pasos del restablecimiento
  const nextStepBtn = document.getElementById("nextStep");
  if (nextStepBtn) {
    nextStepBtn.addEventListener("click", handleResetStep);
  }

  // Reenviar código
  const resendCodeLink = document.getElementById("resendCode");
  if (resendCodeLink) {
    resendCodeLink.addEventListener("click", function (e) {
      e.preventDefault();
      handleStep1();
    });
  }

  // Validar contraseñas en tiempo real
  const confirmPasswordInput = document.getElementById("confirmPassword");
  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener("input", validatePasswords);
  }

  const modalElement = document.getElementById("resetPasswordModal");
  if (modalElement) {
    modalElement.addEventListener("show.bs.modal", function () {
      setTimeout(setupStepNavigation, 500);
    });
  }
}

function validatePasswords() {
  const newPassword = document.getElementById("newPassword").value;
  const confirmPassword = document.getElementById("confirmPassword").value;
  const errorDiv = document.getElementById("passwordError");

  if (errorDiv) {
    if (confirmPassword && newPassword !== confirmPassword) {
      errorDiv.style.display = "block";
    } else {
      errorDiv.style.display = "none";
    }
  }
}

function setupStepNavigation() {
  const steps = document.querySelectorAll(".step.completed");

  steps.forEach((step) => {
    step.addEventListener("click", function () {
      const stepNumber = parseInt(this.dataset.step);
      if (stepNumber < currentStep) {
        goToStep(stepNumber);
      }
    });
  });
}

async function handleResetStep() {
  switch (currentStep) {
    case 1:
      await handleStep1();
      break;
    case 2:
      await handleStep2();
      break;
    case 3:
      await handleStep3();
      break;
  }
  setTimeout(setupStepNavigation, 100);
}

async function handleStep1() {
  const emailInput = document.getElementById("resetEmail");
  if (!emailInput) return;

  const email = emailInput.value;

  if (!isValidEmail(email)) {
    alert("Por favor ingresa un email válido");
    return;
  }

  showLoading();

  try {
    const formData = new FormData();
    formData.append("action", "request_code");
    formData.append("email", email);

    const response = await fetch("auth/controllers/AuthController.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      resetData.email = email;
      resetData.token = result.token;
      goToStep(2);
    } else {
      alert(result.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("Error al procesar la solicitud");
  } finally {
    hideLoading();
  }
}

async function handleStep2() {
  const codeInput = document.getElementById("resetCode");
  if (!codeInput) return;

  const code = codeInput.value;

  if (code.length !== 6) {
    alert("Por favor ingresa el código de 6 dígitos");
    return;
  }

  showLoading();

  try {
    const formData = new FormData();
    formData.append("action", "verify_code");
    formData.append("email", resetData.email);
    formData.append("code", code);
    formData.append("token", resetData.token);

    const response = await fetch("auth/controllers/AuthController.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      resetData.code = code;
      goToStep(3);
    } else {
      alert(result.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("Error al verificar el código");
  } finally {
    hideLoading();
  }
}

async function handleStep3() {
  const newPasswordInput = document.getElementById("newPassword");
  const confirmPasswordInput = document.getElementById("confirmPassword");

  if (!newPasswordInput || !confirmPasswordInput) return;

  const newPassword = newPasswordInput.value;
  const confirmPassword = confirmPasswordInput.value;
  const errorDiv = document.getElementById("passwordError");

  // Validar contraseñas
  if (newPassword.length < 6) {
    alert("La contraseña debe tener al menos 6 caracteres");
    return;
  }

  if (newPassword !== confirmPassword) {
    if (errorDiv) errorDiv.style.display = "block";
    return;
  }

  if (errorDiv) errorDiv.style.display = "none";
  showLoading();

  try {
    const formData = new FormData();
    formData.append("action", "update_password");
    formData.append("email", resetData.email);
    formData.append("code", resetData.code);
    formData.append("token", resetData.token);
    formData.append("new_password", newPassword);

    const response = await fetch("auth/controllers/AuthController.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      alert("¡Contraseña actualizada correctamente!");
      if (resetPasswordModal) {
        resetPasswordModal.hide();
      }
      resetForm();
    } else {
      alert(result.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("Error al actualizar la contraseña");
  } finally {
    hideLoading();
  }
}

function goToStep(step) {
  // Ocultar todos los pasos
  document.querySelectorAll(".reset-step").forEach((div) => {
    div.style.display = "none";
  });

  // Mostrar paso actual
  const currentStepElement = document.getElementById(`step${step}`);
  if (currentStepElement) {
    currentStepElement.style.display = "block";
  }

  // Actualizar botón
  const nextBtn = document.getElementById("nextStep");
  if (nextBtn) {
    nextBtn.textContent = step === 3 ? "Actualizar Contraseña" : "Siguiente";
  }
  updateStepIndicator(step);

  currentStep = step;
}

function resetForm() {
  currentStep = 1;
  resetData = { email: "", token: "", code: "" };
  goToStep(1);

  // Limpiar inputs
  const resetEmail = document.getElementById("resetEmail");
  const resetCode = document.getElementById("resetCode");
  const newPassword = document.getElementById("newPassword");
  const confirmPassword = document.getElementById("confirmPassword");
  const passwordError = document.getElementById("passwordError");

  if (resetEmail) resetEmail.value = "";
  if (resetCode) resetCode.value = "";
  if (newPassword) newPassword.value = "";
  if (confirmPassword) confirmPassword.value = "";
  if (passwordError) passwordError.style.display = "none";
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

function showLoading() {
  const loadingOverlay = document.querySelector(".loading-overlay");
  if (loadingOverlay) {
    loadingOverlay.style.display = "flex";
  }
}

function hideLoading() {
  const loadingOverlay = document.querySelector(".loading-overlay");
  if (loadingOverlay) {
    loadingOverlay.style.display = "none";
  }
}

/* password toogle */

document.addEventListener("DOMContentLoaded", function () {
  initializePasswordToggles();
});

const initializePasswordToggles = () => {
  const toggleConfigs = [
    {
      toggleId: "passwordToggleLogin",
      inputId: "password",
    },
    {
      toggleId: "passwordToggleRes",
      inputId: "newPassword",
    },
    {
      toggleId: "passwordToggleResConfirm",
      inputId: "confirmPassword",
    },
    {
      toggleId: "passwordToggleNew",
      inputId: "passwordNewAccount",
    },
    {
      toggleId: "passwordToggleConfirm",
      inputSelector: 'input[name="confirmPassword"]',
    },
  ];

  toggleConfigs.forEach((config) => {
    const toggle = document.getElementById(config.toggleId);
    let input;

    if (config.inputId) {
      input = document.getElementById(config.inputId);
    } else if (config.inputSelector) {
      input = document.querySelector(config.inputSelector);
    }

    if (toggle && input) {
      setupPasswordToggle(toggle, input);
    }
  });
};

const setupPasswordToggle = (toggleElement, inputElement) => {
  toggleElement.addEventListener("click", function () {
    const type =
      inputElement.getAttribute("type") === "password" ? "text" : "password";
    inputElement.setAttribute("type", type);

    const icon = this.querySelector("i");
    icon.classList.toggle("fa-eye");
    icon.classList.toggle("fa-eye-slash");

    this.style.transform = "scale(0.95)";
    setTimeout(() => {
      this.style.transform = "scale(1)";
    }, 150);
  });
};
