document.addEventListener('DOMContentLoaded', function () {

    const cuadrosPin = document.querySelectorAll('.pin-cuadro');
    console.log('Archivo verificar-pin.js ejecutado');
    console.log('Cantidad de cuadros:', cuadrosPin.length);

    const campoPin = document.getElementById('pin');
    const formularioPin = document.getElementById('formulario-pin');

    console.log('JavaScript del PIN cargado:', cuadrosPin.length);

    // Verificar que existan los seis cuadros.
    if (cuadrosPin.length !== 6 || !campoPin || !formularioPin) {
        console.error('Faltan elementos del formulario PIN.');
        return;
    }

    // Avanzar automáticamente al escribir un número.
    cuadrosPin.forEach(function (cuadro, indice) {

        cuadro.addEventListener('input', function () {

            // Aceptar solamente un dígito numérico.
            this.value = this.value.replace(/[^0-9]/g, '').slice(-1);

            // Actualizar el PIN completo.
            campoPin.value = Array.from(cuadrosPin)
                .map(function (elemento) {
                    return elemento.value;
                })
                .join('');

            // Aplicar estilo al cuadro que ya tiene un número.
            this.classList.toggle(
                'pin-completado',
                this.value !== ''
            );

            // Mover el cursor inmediatamente al siguiente cuadro.
            if (this.value !== '' && indice < cuadrosPin.length - 1) {
                cuadrosPin[indice + 1].focus();
            }
        });

        // Regresar al cuadro anterior si se presiona Retroceso.
        cuadro.addEventListener('keydown', function (evento) {

            if (
                evento.key === 'Backspace' &&
                this.value === '' &&
                indice > 0
            ) {
                cuadrosPin[indice - 1].focus();
            }

            // Permitir navegación con las flechas.
            if (evento.key === 'ArrowLeft' && indice > 0) {
                cuadrosPin[indice - 1].focus();
            }

            if (
                evento.key === 'ArrowRight' &&
                indice < cuadrosPin.length - 1
            ) {
                cuadrosPin[indice + 1].focus();
            }
        });
    });

    // Permitir pegar el PIN completo en el primer cuadro.
    cuadrosPin[0].addEventListener('paste', function (evento) {

        evento.preventDefault();

        const pinPegado = evento.clipboardData
            .getData('text')
            .replace(/\D/g, '')
            .slice(0, 6);

        cuadrosPin.forEach(function (cuadro, indice) {
            cuadro.value = pinPegado[indice] || '';
            cuadro.classList.toggle(
                'pin-completado',
                cuadro.value !== ''
            );
        });

        campoPin.value = pinPegado;

        const cuadroVacio = Array.from(cuadrosPin).find(
            function (cuadro) {
                return cuadro.value === '';
            }
        );

        (cuadroVacio || cuadrosPin[5]).focus();
    });

    // Validar el PIN antes de enviarlo al controlador.
    formularioPin.addEventListener('submit', function (evento) {

        campoPin.value = Array.from(cuadrosPin)
            .map(function (cuadro) {
                return cuadro.value;
            })
            .join('');

        if (!/^[0-9]{6}$/.test(campoPin.value)) {
            evento.preventDefault();
            //alert('Ingresa los seis dígitos del PIN.');
        }
    });

    // Colocar el cursor en el primer cuadro.
    cuadrosPin[0].focus();

});

// Descripción: Oculta automáticamente las alertas después de 5 segundos.
document.addEventListener('DOMContentLoaded', function () {
    const alertasFormulario = document.querySelectorAll('.alerta-auth');

    alertasFormulario.forEach(function (alertaActual) {
        setTimeout(function () {
            alertaActual.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alertaActual.style.opacity = '0';
            alertaActual.style.transform = 'translateY(-10px)';

            setTimeout(function () {
                alertaActual.remove();
            }, 500);
        }, 5000);
    });
});
