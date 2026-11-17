/*
 * Validación en el navegador (ayuda al usuario antes de enviar).
 * NO reemplaza la validación del servidor: cualquiera puede saltarse el JavaScript,
 * por eso EstudianteController vuelve a validar todo.
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-validar]').forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            var edad = form.querySelector('[name="edad"]');

            if (edad) {
                var valor = edad.value.trim();
                var esEntero = /^\d+$/.test(valor);
                var minimo = parseInt(edad.min, 10);
                edad.setCustomValidity(
                    valor === '' ? '' :
                    !esEntero ? 'La edad debe ser un número entero.' :
                    parseInt(valor, 10) < minimo ? 'La edad mínima es ' + minimo + ' años.' : ''
                );
            }

            form.querySelectorAll('input, select').forEach(function (campo) {
                if (campo.type !== 'hidden' && campo.value.trim() === '' && campo.required) {
                    campo.setCustomValidity('Este campo es obligatorio.');
                } else if (campo.name !== 'edad') {
                    campo.setCustomValidity('');
                }
            });

            if (!form.checkValidity()) {
                evento.preventDefault();
                evento.stopPropagation();
                form.reportValidity();
            }
            form.classList.add('was-validated');
        });

        // Al corregir un campo, se limpia su mensaje personalizado.
        form.querySelectorAll('input, select').forEach(function (campo) {
            campo.addEventListener('input', function () { campo.setCustomValidity(''); });
            campo.addEventListener('change', function () { campo.setCustomValidity(''); });
        });
    });
});
