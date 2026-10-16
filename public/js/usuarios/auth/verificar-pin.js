// Descripción: Configura el comportamiento de los campos para ingresar y validar el PIN.
document.addEventListener('DOMContentLoaded', function () {

    // Obtener los seis cuadros individuales del PIN.
    const cuadrosPin = document.querySelectorAll('.pin-cuadro');
    console.log('Archivo verificar-pin.js ejecutado');
    console.log('Cantidad de cuadros:', cuadrosPin.length);

    // Obtener el campo oculto que contiene el PIN completo. 
    const campoPin = document.getElementById('pin');
    // Obtener el formulario de verificación del PIN.
    const formularioPin = document.getElementById('formulario-pin');

    console.log('JavaScript del PIN cargado:', cuadrosPin.length);

    // Verificar que existan los seis cuadros.
    if (cuadrosPin.length !== 6 || !campoPin || !formularioPin) {
        console.error('Faltan elementos del formulario PIN.');
        return;
    }

    // Avanzar automáticamente al escribir un número.
    cuadrosPin.forEach(function (cuadro, indice) {

        // Detectar cuando el usuario escribe un número.
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

        // Detectar las teclas utilizadas para navegar entre los cuadros.
        cuadro.addEventListener('keydown', function (evento) {

            // Regresar al cuadro anterior si se presiona Retroceso.
            if (
                evento.key === 'Backspace' &&
                this.value === '' &&
                indice > 0
            ) {
                cuadrosPin[indice - 1].focus();
            }

            // Permitir navegación con las flechas. 
            // Moverse al cuadro anterior con la flecha izquierda.
            if (evento.key === 'ArrowLeft' && indice > 0) {
                cuadrosPin[indice - 1].focus();
            }

            // Moverse al cuadro siguiente con la flecha derecha.
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

        // Evitar el comportamiento predeterminado del pegado.
        evento.preventDefault();
        
        // Obtener únicamente los números del PIN pegado.
        const pinPegado = evento.clipboardData
            .getData('text')
            .replace(/\D/g, '')
            .slice(0, 6);

        // Distribuir los dígitos entre los seis cuadros.
        cuadrosPin.forEach(function (cuadro, indice) {
            cuadro.value = pinPegado[indice] || '';

            // Aplicar el estilo cuando el cuadro contiene un número.
            cuadro.classList.toggle(
                'pin-completado',
                cuadro.value !== ''
            );
        });

        // Actualizar el campo oculto con el PIN completo.
        campoPin.value = pinPegado;

        // Buscar el primer cuadro que todavía esté vacío.
        const cuadroVacio = Array.from(cuadrosPin).find(
            function (cuadro) {
                return cuadro.value === '';
            }
        );

        // Colocar el cursor en el primer cuadro vacío o en el último.
        (cuadroVacio || cuadrosPin[5]).focus();
    });

    // Validar el PIN antes de enviarlo al controlador.
    formularioPin.addEventListener('submit', function (evento) {

        // Construir nuevamente el PIN con los valores de los seis cuadros.
        campoPin.value = Array.from(cuadrosPin)
            .map(function (cuadro) {
                return cuadro.value;
            })
            .join('');

        // Verificar que el PIN contenga exactamente seis dígitos.
        if (!/^[0-9]{6}$/.test(campoPin.value)) {
            evento.preventDefault();
        }
    });

    // Colocar el cursor en el primer cuadro.
    cuadrosPin[0].focus();

});

// Descripción: Oculta automáticamente las alertas después de 5 segundos.
document.addEventListener('DOMContentLoaded', function () {
    const alertasFormulario = document.querySelectorAll('.alerta-auth');

    alertasFormulario.forEach(function (alertaActual) {
        
        // Esperar cinco segundos antes de ocultar la alerta.
        setTimeout(function () {
            alertaActual.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alertaActual.style.opacity = '0';
            alertaActual.style.transform = 'translateY(-10px)';

            // Eliminar la alerta después de terminar la transición.
            setTimeout(function () {
                alertaActual.remove();
            }, 500);
        }, 5000);
    });
});
