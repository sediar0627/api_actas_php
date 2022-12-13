let mensaje_sesion = localStorage.getItem('mensaje');

if (mensaje_sesion) {
    mensaje_sesion = JSON.parse(mensaje_sesion);

    Swal.fire({
        icon: mensaje_sesion.icono,
        title: mensaje_sesion.texto,
        showConfirmButton: false,
        timer: 3000
    });

    localStorage.removeItem('mensaje');
}

let usuario_logueado = localStorage.getItem('usuario');

if (! usuario_logueado && ! window.location.href.includes("login.html")) {
    localStorage.setItem('mensaje', JSON.stringify({icono: 'error', texto: 'Debe iniciar sesión.'}));
    window.location.href = "login.html";
}

usuario_logueado = JSON.parse(usuario_logueado);

if(
    usuario_logueado && ! usuario_logueado.correo_verificado && 
    ! window.location.href.includes("verificar_correo.html") && 
    ! window.location.href.includes("login.html")
){
    // localStorage.setItem('mensaje', JSON.stringify({icono: 'info', texto: 'Debe verificar su correo. Se le ha enviado un correo de verificación'}));
    window.location.href = "verificar_correo.html";
}