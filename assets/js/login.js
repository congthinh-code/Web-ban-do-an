document.addEventListener('DOMContentLoaded', function() {
    const signUpBtn = document.getElementById('signUp');
    const signInBtn = document.getElementById('signIn');

    if (signUpBtn) {
        signUpBtn.addEventListener('click', function() {
            document.title = 'Đăng ký';
        });
    }
    if (signInBtn) {
        signInBtn.addEventListener('click', function() {
            document.title = 'Đăng nhập';
        });
    }
});const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('container');

    signUpButton.addEventListener('click', () => {
      container.classList.add("right-panel-active");
    });

    signInButton.addEventListener('click', () => {
      container.classList.remove("right-panel-active");
    });