document.getElementById('registerForm').addEventListener('submit', function (e) {
    e.preventDefault();
    e.stopPropagation();

    var isValid = true;

    // Gather and validate form data
    var formData = {
        fullname: validateField('fullname', value => value.trim() !== ''),
        email: validateField('email', isValidEmail),
        password: validateField('password', value => value.length > 8)
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
        xhr.open('POST', 'http://localhost:4000/api.php/register', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                alert('Cadastro realizado!')
                window.location.href = '../login.html';
            }
        };
        xhr.send(JSON.stringify(formData));
    }

    function isValidEmail(email) {
        var re = /\S+@\S+\.\S+/;
        return re.test(email);
    }
});
