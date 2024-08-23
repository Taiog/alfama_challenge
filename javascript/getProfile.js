document.addEventListener('DOMContentLoaded', function () {

    var userId = localStorage.getItem('userId');
    if (!userId) {
        window.location.href = 'login.html';
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'http://localhost:4000/api.php/me', true);
    xhr.setRequestHeader('User-Id', userId);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                var userData = JSON.parse(xhr.responseText);
                document.getElementById('fullname').value = userData.user.fullname;
                document.getElementById('email').value = userData.user.email;
                document.getElementById('phone').value = userData.user.phone;
                document.getElementById('address').value = userData.user.address;
                document.getElementById('company').value = userData.user.company;
                document.getElementById('cpf').value = userData.user.cpf;
                document.getElementById('user-name').textContent = userData.user.fullname;
            } else {
                console.error('Erro ao obter dados do usuário:', xhr.status);
                window.location.href = 'login.html';
            }
        }
    };
    xhr.send();
});

const logout = document.getElementById('logout');

logout.addEventListener('click', () => {
    localStorage.removeItem('userId');
});

document.getElementById('cpf').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    e.target.value = value;
});

document.getElementById('phone').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{2})(\d)/, '($1) $2');
    value = value.replace(/(\d{5})(\d)/, '$1-$2');
    value = value.slice(0, 15);
    e.target.value = value;
});