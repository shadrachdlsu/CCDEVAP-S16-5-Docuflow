(() => {
  const table = document.querySelector('#adminUsersTable');
  const modal = document.querySelector('#resetPasswordModal');
  const userIdInput = document.querySelector('#resetPasswordUserId');
  const userName = document.querySelector('#resetPasswordUserName');
  const newPassword = document.querySelector('#newPassword');
  let lastTrigger = null;

  const closeMenus = (except = null) => {
    document.querySelectorAll('.admin-action-menu-panel:not([hidden])').forEach((panel) => {
      if (panel === except) {
        return;
      }
      panel.hidden = true;
      panel.previousElementSibling?.setAttribute('aria-expanded', 'false');
    });
  };

  const positionMenu = (trigger, panel) => {
    const triggerRect = trigger.getBoundingClientRect();
    const panelRect = panel.getBoundingClientRect();
    const spacing = 6;
    const left = Math.max(8, triggerRect.right - panelRect.width);
    const opensUpward = triggerRect.bottom + spacing + panelRect.height > window.innerHeight;

    panel.style.left = `${left}px`;
    panel.style.top = `${opensUpward
      ? Math.max(8, triggerRect.top - panelRect.height - spacing)
      : triggerRect.bottom + spacing}px`;
  };

  const closeModal = () => {
    if (!modal) {
      return;
    }
    modal.hidden = true;
    document.body.classList.remove('admin-modal-open');
    modal.querySelector('form')?.reset();
    lastTrigger?.focus();
  };

  table?.addEventListener('click', (event) => {
    const menuTrigger = event.target.closest('.admin-action-menu-trigger');
    if (menuTrigger) {
      const panel = menuTrigger.nextElementSibling;
      const willOpen = panel.hidden;
      closeMenus(panel);
      panel.hidden = !willOpen;
      menuTrigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      if (willOpen) {
        positionMenu(menuTrigger, panel);
      }
      return;
    }

    const resetTrigger = event.target.closest('.admin-reset-password-trigger');
    if (resetTrigger && modal) {
      closeMenus();
      lastTrigger = resetTrigger;
      userIdInput.value = resetTrigger.dataset.userId || '';
      userName.textContent = resetTrigger.dataset.userName || 'this user';
      modal.hidden = false;
      document.body.classList.add('admin-modal-open');
      window.setTimeout(() => newPassword?.focus(), 0);
      return;
    }

    const deleteButton = event.target.closest('.admin-delete-user-form button[type="submit"]');
    if (deleteButton && !window.confirm(`Delete ${deleteButton.dataset.userName || 'this user'}? This is only allowed when the account has no document or office activity.`)) {
      event.preventDefault();
    }
  });

  document.addEventListener('click', (event) => {
    if (!event.target.closest('.admin-action-menu')) {
      closeMenus();
    }
    if (event.target.closest('[data-close-modal]')) {
      closeModal();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
      return;
    }
    closeMenus();
    if (modal && !modal.hidden) {
      closeModal();
    }
  });

  window.addEventListener('resize', () => closeMenus());
  window.addEventListener('scroll', () => closeMenus(), true);
})();
