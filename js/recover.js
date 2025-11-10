const loadingOverlay = document.querySelector(".loading-overlay");

class AccountRecovery {
  constructor() {
    this.recoveryToken = null;
    this.finalToken = null;
    this.currentStep = 1;
  }

  async initiateRecovery(email) {
    try {
      const formData = new FormData();
      formData.append("action", "initiate_account_recovery");
      formData.append("email", email);
      loadingOverlay.style.display = "flex";

      const response = await fetch(
        "/auth/controllers/AuthRecoveryController.php",
        {
          method: "POST",
          body: formData,
        }
      );

      const result = await response.json();

      if (result.success) {
        this.recoveryToken = result.recovery_token;
        this.showStep(2);
        this.showResult("✅ Código enviado a tu email", "success");
      } else {
        this.showResult("❌ " + result.message, "danger");
      }
    } catch (error) {
      this.showResult("❌ Error de conexión", "danger");
    } finally {
        loadingOverlay.style.display = "none";
    }
  }

  async verifyCode(code) {
    try {
      const formData = new FormData();
      formData.append("action", "verify_recovery_identity");
      formData.append("token", this.recoveryToken);
      formData.append("code", code);
      loadingOverlay.style.display = "flex";

      const response = await fetch(
        "/auth/controllers/AuthRecoveryController.php",
        {
          method: "POST",
          body: formData,
        }
      );

      const result = await response.json();

      if (result.success) {
        this.finalToken = result.final_token;
        this.showStep(3);
        this.showResult("✅ Identidad verificada correctamente", "success");
      } else {
        this.showResult("❌ " + result.message, "danger");
      }
    } catch (error) {
      this.showResult("❌ Error de conexión", "danger");
    } finally {
        loadingOverlay.style.display = "none";
    }
  }

  async recoverAccount(newPassword, confirmPassword) {
    if (newPassword !== confirmPassword) {
      this.showResult("❌ Las contraseñas no coinciden", "danger");
      return;
    }

    try {
      const formData = new FormData();
      formData.append("action", "recover_account");
      formData.append("final_token", this.finalToken);
      formData.append("new_password", newPassword);
loadingOverlay.style.display = "flex";
      const response = await fetch(
        "/auth/controllers/AuthRecoveryController.php",
        {
          method: "POST",
          body: formData,
        }
      );

      const result = await response.json();

      if (result.success) {
        this.showResult("✅ " + result.message, "success");
        setTimeout(() => {
          window.location.href = "auth/SignOut.php";
        }, 3000);
      } else {
        this.showResult("❌ " + result.message, "danger");
      }
    } catch (error) {
      this.showResult("❌ Error de conexión", "danger");
    } finally {
        loadingOverlay.style.display = "none";
    }
  }

  showStep(step) {
    document.querySelectorAll(".recovery-step").forEach((el) => {
      el.classList.remove("active");
    });
    document.getElementById(`step${step}`).classList.add("active");
    this.currentStep = step;
  }

  showResult(message, type) {
    const resultDiv = document.getElementById("resultMessage");
    resultDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
  }
}

// Inicializar
const recovery = new AccountRecovery();

// Event Listeners
document
  .getElementById("initiateRecoveryForm")
  .addEventListener("submit", function (e) {
    e.preventDefault();
    const email = this.querySelector("input").value;
    recovery.initiateRecovery(email);
  });

document
  .getElementById("verifyCodeForm")
  .addEventListener("submit", function (e) {
    e.preventDefault();
    const code = this.querySelector("input").value;
    recovery.verifyCode(code);
  });

document
  .getElementById("newPasswordForm")
  .addEventListener("submit", function (e) {
    e.preventDefault();
    const inputs = this.querySelectorAll("input");
    recovery.recoverAccount(inputs[0].value, inputs[1].value);
  });
