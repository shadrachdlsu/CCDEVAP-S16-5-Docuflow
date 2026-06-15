document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;

    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark-mode');
        
        if (body.classList.contains('dark-mode')) {
            themeToggle.textContent = '☀️';
        } else {
            themeToggle.textContent = '🌙';
        }
    });

    const form = document.getElementById('signupForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const messageDiv = document.getElementById('message');

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        messageDiv.textContent = '';
        messageDiv.className = 'message';

        const email = emailInput.value;
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (password !== confirmPassword) {
            messageDiv.textContent = "Passwords do not match!";
            messageDiv.classList.add('error');
            return;
        }

        if (password.length < 6) {
            messageDiv.textContent = "Password must be at least 6 characters.";
            messageDiv.classList.add('error');
            return;
        }

        messageDiv.textContent = `Success! Account created for ${email}`;
        messageDiv.classList.add('success');

        form.reset();
    });
});