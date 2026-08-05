(function () {
  "use strict";

  const pageName = () => (
    window.location.pathname.split("/").pop() || ""
  ).toLowerCase();

  const activeKeyForPage = (page, role) => {
    if (role === "admin") {
      if (page === "admin-dashboard.php") return "dashboard";
      if (page === "admin-reports.php" || page === "admin-documents-created-report.php") return "reports";
      if (page === "admin-manage-users.php" || page === "admin-user.php") return "users";
      if (page === "admin-manage-offices.php" || page === "admin-office.php") return "offices";
      if (page === "admin-manage-document-types.php") return "types";
      if (page === "admin-settings.php") return "settings";
      if (page === "admin-documents.php" || page === "admin-document.php") return "documents";
      return "";
    }

    if (page === "secretary-dashboard.php" || page === "member-dashboard.php") return "dashboard";
    if (page === "member-create-document.php") return "create";
    if (page === "member-my-documents.php") return "mine";
    if (page === "member-reports.php") return "reports";
    if (page === "secretary-assign-documents.php" || page === "secretary-assign-document.php") return "assign";
    if (page === "admin-manage-document-types.php") return "types";
    if (page === "member-documents.php" || page === "member-document.php" || page === "member-document-trail.php") return "inbox";
    return "";
  };

  const roleForHeader = (header) => {
    const page = pageName();
    const roleText = header.querySelector(".user-role")?.textContent?.toLowerCase() || "";
    const logoHref = header.querySelector(".web-logo")?.getAttribute("href")?.toLowerCase() || "";

    if (page.startsWith("secretary-") || roleText.includes("secretary") || logoHref.includes("secretary-dashboard")) {
      return "secretary";
    }

    if (header.classList.contains("admin-header")) {
      return "admin";
    }

    return "member";
  };

  const linksForRole = (role) => {
    if (role === "admin") {
      return [
        ["dashboard", "Dashboard", "admin-dashboard.php"],
        ["documents", "Documents", "admin-documents.php"],
        ["reports", "Reports", "admin-reports.php"],
        ["users", "Manage Users", "admin-manage-users.php"],
        ["offices", "Manage Offices", "admin-manage-offices.php"],
        ["types", "Document Types", "admin-manage-document-types.php"],
        ["settings", "System Settings", "admin-settings.php"]
      ];
    }

    const links = [
      ["dashboard", "Dashboard", role === "secretary" ? "secretary-dashboard.php" : "member-dashboard.php"],
      ["create", "Create Document", "member-create-document.php"],
      ["inbox", "Inbox", "member-documents.php"],
      ["mine", "My Documents", "member-my-documents.php"]
    ];

    if (role === "secretary") {
      links.push(["assign", "Assign Documents", "secretary-assign-documents.php"]);
    }

    links.push(["reports", "Reports", "member-reports.php"]);

    if (role === "secretary") {
      links.push(["types", "Document Types", "admin-manage-document-types.php"]);
    }

    return links;
  };

  const normalizeLogo = (headerLeft, homePage) => {
    const logo = headerLeft.querySelector(".web-logo");

    if (!logo) return;

    logo.textContent = "DocuFlow";

    if (logo.tagName === "A") {
      logo.href = homePage;
      logo.classList.add("logo-area");
      return;
    }

    const oldContainer = logo.parentElement;
    const link = document.createElement("a");
    link.className = "logo-area";
    link.href = homePage;
    link.append(logo);

    if (oldContainer?.classList.contains("logo-area") && oldContainer.children.length === 0) {
      oldContainer.replaceWith(link);
      return;
    }

    headerLeft.prepend(link);
  };

  const buildProfileMenu = (headerRight, actions) => {
    if (actions.querySelector("[data-profile-toggle]")) return;

    const email = headerRight.querySelector(".user-email")?.textContent?.trim() || "Signed-in user";
    const role = headerRight.querySelector(".user-role")?.textContent?.trim() || "DocuFlow user";
    const menu = document.createElement("div");
    menu.className = "profile-menu";
    menu.innerHTML = `
      <button class="icon-btn icon-button" type="button" data-profile-toggle aria-label="Open profile summary" aria-expanded="false">
        <i class="fas fa-user-circle" aria-hidden="true"></i>
      </button>
      <div class="profile-popover" data-profile-popover>
        <strong>${escapeHtml(email)}</strong>
        <span>Signed in to DocuFlow</span>
        <span class="profile-role">${escapeHtml(role)}</span>
      </div>
    `;

    actions.prepend(menu);

    const toggle = menu.querySelector("[data-profile-toggle]");
    const popover = menu.querySelector("[data-profile-popover]");

    toggle.addEventListener("click", () => {
      const open = popover.classList.toggle("open");
      toggle.setAttribute("aria-expanded", String(open));
    });

    document.addEventListener("click", (event) => {
      if (!menu.contains(event.target)) {
        popover.classList.remove("open");
        toggle.setAttribute("aria-expanded", "false");
      }
    });

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape") {
        popover.classList.remove("open");
        toggle.setAttribute("aria-expanded", "false");
        toggle.focus();
      }
    });
  };

  const escapeHtml = (value) => {
    const element = document.createElement("span");
    element.textContent = value;
    return element.innerHTML;
  };

  const initialize = () => {
    const header = document.querySelector(".admin-header, .member-header");

    if (!header || header.dataset.navigationReady === "true") return;

    const role = roleForHeader(header);
    const page = pageName();
    const activeKey = activeKeyForPage(page, role);
    const links = linksForRole(role);
    const homePage = links[0][2];
    let headerLeft = header.querySelector(".header-left");

    header.dataset.navigationReady = "true";
    header.classList.add("docuflow-header");

    if (!headerLeft) {
      headerLeft = document.createElement("div");
      headerLeft.className = "header-left";
      const existingLogo = header.querySelector(".web-logo");

      if (existingLogo) {
        headerLeft.append(existingLogo);
      }

      header.prepend(headerLeft);
    }

    normalizeLogo(headerLeft, homePage);
    headerLeft.querySelector(".header-nav")?.remove();

    const nav = document.createElement("nav");
    nav.className = "header-nav";
    nav.setAttribute("aria-label", `${role === "admin" ? "Administrator" : role === "secretary" ? "Secretary" : "Member"} navigation`);

    links.forEach(([key, label, href]) => {
      const link = document.createElement("a");
      link.className = "header-nav-item";
      link.href = href;
      link.textContent = label;

      if (key === activeKey) {
        link.classList.add("active");
        link.setAttribute("aria-current", "page");
      }

      nav.append(link);
    });

    headerLeft.append(nav);

    const headerRight = header.querySelector(".header-right");
    if (!headerRight) return;

    let actions = headerRight.querySelector(".header-actions");

    if (!actions) {
      actions = document.createElement("div");
      actions.className = "header-actions";

      Array.from(headerRight.children)
        .filter((element) => !element.classList.contains("user-info"))
        .forEach((element) => actions.append(element));

      headerRight.append(actions);
    }

    buildProfileMenu(headerRight, actions);
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initialize, { once: true });
  } else {
    initialize();
  }
})();
