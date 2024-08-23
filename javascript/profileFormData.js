document.getElementById('profileForm').addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent the form from submitting normally
    e.stopPropagation();

    var userId = localStorage.getItem('userId');
    if (!userId) {
        window.location.href = 'login.html';
        return;
    }

    // Gather the form data
    var form = this;
    var isValid = true;

    // Gather and validate form data
    var formData = {
        fullname: validateField('fullname', value => value.trim() !== ''),
        email: validateField('email', isValidEmail),
        phone: validateField('phone', value => value.replace(/\D/g, '').length === 11),
        address: validateField('address', value => value.trim() !== ''),
        company: validateField('company', value => value.trim() !== ''),
        cpf: validateField('cpf', value => value.replace(/\D/g, '').length === 11)
    };

    function validateField(fieldName, validationFn) {
        var field = document.getElementById(fieldName);
        var value = field.value;
        if (validationFn(value)) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            return value;
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            isValid = false;
            return null;
        }
    }

    this.classList.add('was-validated');

    if (this.checkValidity()) {
        var xhr = new XMLHttpRequest();
        xhr.open('PUT', 'http://localhost:4000/api.php/update', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('User-Id', userId);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                alert('Perfil atualizado!')
            }
        };
        xhr.send(JSON.stringify(formData));
    }

    function isValidEmail(email) {
        var re = /\S+@\S+\.\S+/;
        return re.test(email);
    }
});
