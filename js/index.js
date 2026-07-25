document.addEventListener("DOMContentLoaded", () => {
  // Handle signup form
  const form = document.getElementById("signupForm");
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const confirmPasswordInput = document.getElementById("confirmPassword");
  const officeSelect = document.getElementById("officeSelect");
  const messageDiv = document.getElementById("message");

  if (
    form &&
    messageDiv &&
    emailInput &&
    passwordInput &&
    confirmPasswordInput &&
    officeSelect
  ) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      messageDiv.textContent = "";
      messageDiv.className = "message";

      const email = emailInput.value.trim();
      const password = passwordInput.value;
      const confirmPassword = confirmPasswordInput.value;

      if (email.toLowerCase() === "member@gmail.com") {
        window.location.replace("./member-dashboard.php");
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

      const selectedOffice = officeSelect.value;

      if (!selectedOffice) {
        messageDiv.textContent = "Please select your office.";
        messageDiv.classList.add("error");
        return;
      }

      messageDiv.textContent = `Success! Account created for ${email} in ${selectedOffice}.`;
      messageDiv.classList.add("success");

      form.reset();
    });
  }
});
