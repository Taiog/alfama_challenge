document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();

    var formData = {
        email: document.getElementById('email').value,
        password: document.getElementById('password').value
    };

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'http://localhost:4000/api.php/login', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onreadystatechange = function () {
        var response = JSON.parse(xhr.responseText);
        if (xhr.readyState === 4 && xhr.status === 200) {
            if (response.userId) {
                localStorage.setItem('userId', response.userId);
                window.location.href = 'index.html';
            }
        } else {
            var field = document.getElementById('invalid-form');
            field.classList.remove('d-none')
            field.textContent = 'Email ou senha inválidos'
        }
    };
    xhr.send(JSON.stringify(formData));
});