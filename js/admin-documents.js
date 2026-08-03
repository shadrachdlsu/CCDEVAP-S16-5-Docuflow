"use strict";

document.addEventListener("DOMContentLoaded", () => {

    const themeToggle = document.getElementById("themeToggle");
    const logoutButton = document.querySelector(".logout-btn");

    /*
    |--------------------------------------------------------------------------
    | DATATABLE INITIALIZATION
    |--------------------------------------------------------------------------
    */

    if ($.fn.DataTable.isDataTable("#documentsTable")) {
        $("#documentsTable").DataTable().destroy();
    }

    $("#documentsTable").DataTable();

    /*
    |--------------------------------------------------------------------------
    | THEME & LOGOUT HANDLERS
    |--------------------------------------------------------------------------
    */

    if (localStorage.getItem("docuflow-theme") === "dark") {
        document.body.classList.add("dark-mode");
    }

    if (themeToggle) {
        const icon = themeToggle.querySelector("i");

        if (document.body.classList.contains("dark-mode")) {
            icon.classList.remove("fa-moon");
            icon.classList.add("fa-sun");
        } else {
            icon.classList.remove("fa-sun");
            icon.classList.add("fa-moon");
        }

        themeToggle.addEventListener("click", () => {
            document.body.classList.toggle("dark-mode");
            const isDark = document.body.classList.contains("dark-mode");
            localStorage.setItem("docuflow-theme", isDark ? "dark" : "light");

            if (isDark) {
                icon.classList.remove("fa-moon");
                icon.classList.add("fa-sun");
            } else {
                icon.classList.remove("fa-sun");
                icon.classList.add("fa-moon");
            }
        });
    }

    if (logoutButton) {
        logoutButton.addEventListener("click", () => {
            window.location.href = "../controllers/LogoutController.php";
        });
    }
});
