// Password Show/Hide and Form Toggle Script

document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.container');
    const pwShowHide = document.querySelectorAll('.showHidePw');
    const pwFields = document.querySelectorAll('#password');
    const signUp = document.querySelector('.signup-link');
    const login = document.querySelector('.login-link');

    // Show/hide password and change icon
    pwShowHide.forEach(eyeIcon => {
        eyeIcon.addEventListener('click', () => {
            pwFields.forEach(pwField => {
                const isPassword = pwField.type === 'password';
                pwField.type = isPassword ? 'text' : 'password';
                pwShowHide.forEach(icon => {
                    icon.classList.replace(
                        isPassword ? 'uil-eye-slash' : 'uil-eye',
                        isPassword ? 'uil-eye' : 'uil-eye-slash'
                    );
                });
            });
        });
    });

    // Appear signup and login form
    if (signUp) {
        signUp.addEventListener('click', () => {
            container.classList.add('active');
        });
    }
    if (login) {
        login.addEventListener('click', () => {
            container.classList.remove('active');
        });
    }
}); 