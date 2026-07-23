document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("themeToggle");
  const logoutButton = document.querySelector(".logout-btn");
  const sharedThemeKey = "docuflow-theme";
  const memberThemeKey = "docuflow-member-theme";

  const setThemeIcon = () => {
    const icon = themeToggle?.querySelector("i");
    const isLightMode = document.body.classList.contains("light-mode");

    icon?.classList.toggle("fa-sun", !isLightMode);
    icon?.classList.toggle("fa-moon", isLightMode);
  };

  const savedTheme = localStorage.getItem(sharedThemeKey)
    ?? localStorage.getItem(memberThemeKey);

  if (savedTheme === "light") {
    document.body.classList.add("light-mode");
  }

  setThemeIcon();

  themeToggle?.addEventListener("click", () => {
    document.body.classList.toggle("light-mode");
    const theme = document.body.classList.contains("light-mode") ? "light" : "dark";
    localStorage.setItem(memberThemeKey, theme);
    localStorage.setItem(sharedThemeKey, theme);
    setThemeIcon();
  });

  logoutButton?.addEventListener("click", () => {
    if (confirm("Are you sure you want to logout?")) {
      window.location.href = "login.php";
    }
  });
});
