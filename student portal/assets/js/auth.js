// Form validation and enhancement
document.addEventListener("DOMContentLoaded", () => {
  const signupForm = document.getElementById("signupForm")

  if (signupForm) {
    const passwordField = document.getElementById("password")
    const confirmPasswordField = document.getElementById("confirm_password")

    // Real-time password confirmation validation
    confirmPasswordField.addEventListener("input", function () {
      if (this.value !== passwordField.value) {
        this.setCustomValidity("Passwords do not match")
        this.style.borderColor = "#e74c3c"
      } else {
        this.setCustomValidity("")
        this.style.borderColor = "#27ae60"
      }
    })

    // Password strength indicator
    passwordField.addEventListener("input", function () {
      const password = this.value
      const strengthIndicator = document.getElementById("password-strength") || createStrengthIndicator()

      let strength = 0
      const feedback = []

      if (password.length >= 8) strength++
      else feedback.push("At least 8 characters")

      if (/[A-Z]/.test(password)) strength++
      else feedback.push("One uppercase letter")

      if (/[a-z]/.test(password)) strength++
      else feedback.push("One lowercase letter")

      if (/[0-9]/.test(password)) strength++
      else feedback.push("One number")

      if (/[^A-Za-z0-9]/.test(password)) strength++
      else feedback.push("One special character")

      updateStrengthIndicator(strengthIndicator, strength, feedback)
    })

    function createStrengthIndicator() {
      const indicator = document.createElement("div")
      indicator.id = "password-strength"
      indicator.className = "password-strength"
      passwordField.parentNode.appendChild(indicator)
      return indicator
    }

    function updateStrengthIndicator(indicator, strength, feedback) {
      const colors = ["#e74c3c", "#e67e22", "#f39c12", "#27ae60", "#2ecc71"]
      const labels = ["Very Weak", "Weak", "Fair", "Good", "Strong"]

      indicator.style.color = colors[strength - 1] || colors[0]
      indicator.textContent = `Password Strength: ${labels[strength - 1] || labels[0]}`

      if (feedback.length > 0 && strength < 3) {
        indicator.textContent += ` (Need: ${feedback.join(", ")})`
      }
    }
  }

  // Form submission enhancement
  const forms = document.querySelectorAll("form")
  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const submitBtn = form.querySelector('button[type="submit"]')
      if (submitBtn) {
        submitBtn.disabled = true
        submitBtn.textContent = "Please wait..."

        // Re-enable after 3 seconds to prevent permanent disable on validation errors
        setTimeout(() => {
          submitBtn.disabled = false
          submitBtn.textContent = submitBtn.getAttribute("data-original-text") || "Submit"
        }, 3000)
      }
    })
  })
})

// Utility functions
function showAlert(message, type = "info") {
  const alertDiv = document.createElement("div")
  alertDiv.className = `alert alert-${type}`
  alertDiv.textContent = message

  const container = document.querySelector(".auth-card") || document.querySelector(".main-content")
  container.insertBefore(alertDiv, container.firstChild)

  // Auto-remove after 5 seconds
  setTimeout(() => {
    alertDiv.remove()
  }, 5000)
}

// Session timeout warning
let sessionTimeout
function resetSessionTimeout() {
  clearTimeout(sessionTimeout)
  // Warn user 5 minutes before session expires (assuming 30-minute sessions)
  sessionTimeout = setTimeout(
    () => {
      if (confirm("Your session will expire soon. Do you want to stay logged in?")) {
        // Make a request to refresh session
        fetch("refresh_session.php", { method: "POST" })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              resetSessionTimeout()
            }
          })
      }
    },
    25 * 60 * 1000,
  ) // 25 minutes
}

// Initialize session timeout if user is logged in
if (document.body.classList.contains("logged-in")) {
  resetSessionTimeout()

  // Reset timeout on user activity
  ;["click", "keypress", "scroll", "mousemove"].forEach((event) => {
    document.addEventListener(event, resetSessionTimeout)
  })
}
