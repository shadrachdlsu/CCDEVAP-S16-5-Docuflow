document.addEventListener("DOMContentLoaded", () => {
  // Handle signup form
  const form = document.getElementById("signupForm");
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const confirmPasswordInput = document.getElementById("confirmPassword");
  const messageDiv = document.getElementById("message");

  if (
    form &&
    messageDiv &&
    emailInput &&
    passwordInput &&
    confirmPasswordInput
  ) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      messageDiv.textContent = "";
      messageDiv.className = "message";

      const email = emailInput.value.trim();
      const password = passwordInput.value;
      const confirmPassword = confirmPasswordInput.value;

      if (email.toLowerCase() === "member@gmail.com") {
        window.location.replace("./member-dashboard.html");
        return;
      }

      if (password !== confirmPassword) {
        messageDiv.textContent = "Passwords do not match!";
        messageDiv.classList.add("error");
        return;
      }

      if (password.length < 6) {
        messageDiv.textContent = "Password must be at least 6 characters.";
        messageDiv.classList.add("error");
        return;
      }

      messageDiv.textContent = `Success! Account created for ${email}`;
      messageDiv.classList.add("success");

      form.reset();
    });
  }

  // Handle login form
  const loginForm = document.querySelector("form:not(#signupForm)");
  const loginEmailInput = loginForm ? loginForm.querySelector("#email") : null;
  const loginPasswordInput = loginForm
    ? loginForm.querySelector("#password")
    : null;

  if (loginForm && loginEmailInput && loginPasswordInput) {
    loginForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const email = loginEmailInput.value.trim();
      const password = loginPasswordInput.value;

      // Demo credentials
      const validCredentials = {
        "admin@office.gov": "admin123",
        "secretary@office.gov": "secretary123",
        "member@office.gov": "member123",
      };

      if (validCredentials[email] === password) {
        // Login successful - redirect to dashboard
        if (email === "member@office.gov") {
          window.location.replace("./member-dashboard.html");
        } else if (email === "secretary@office.gov") {
          window.location.replace("./secretary-dashboard.html");
        } else if (email === "admin@office.gov") {
          window.location.replace("./admin-dashboard.html");
        }
      } else {
        alert("Invalid email or password. Please try again.");
      }
    });
  }
});
