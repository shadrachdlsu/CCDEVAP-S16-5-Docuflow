document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("themeToggle");
  const sharedThemeKey = "docuflow-theme";
  const adminThemeKey = "docuflow-admin-theme";

  const updateThemeIcon = () => {
    const icon = themeToggle?.querySelector("i");
    const isDarkMode = document.body.classList.contains("dark-mode");

    icon?.classList.toggle("fa-moon", !isDarkMode);
    icon?.classList.toggle("fa-sun", isDarkMode);
  };

  const savedTheme = localStorage.getItem(sharedThemeKey)
    ?? localStorage.getItem(adminThemeKey);

  if (savedTheme === "dark") {
    document.body.classList.add("dark-mode");
  }

  updateThemeIcon();

  themeToggle?.addEventListener("click", () => {
    document.body.classList.toggle("dark-mode");
    const theme = document.body.classList.contains("dark-mode") ? "dark" : "light";
    localStorage.setItem(adminThemeKey, theme);
    localStorage.setItem(sharedThemeKey, theme);
    updateThemeIcon();
  });
});
