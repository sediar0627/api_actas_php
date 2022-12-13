<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

if(! isset($_GET['controlador']) || ! isset($_GET['accion'])){
    echo json_encode([
        'estado' => 400,
        'error' => 'bad_request',
        'mensaje' => 'No se ha especificado el controlador o la accion'
    ]);

    return;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/routes.php';

$controlador = $_GET['controlador'];
$accion = $_GET['accion'];

if(! isset($rutas[$controlador]) || ! isset($rutas[$controlador]['acciones'][$accion])){
    echo json_encode([
        'estado' => 404,
        'error' => 'not_found',
        'mensaje' => 'El controlador o la accion no existe'
    ]);

    return;
}

// Establecer la zona horaria
date_default_timezone_set('America/Bogota');

// Incializar la sesion
session_start();

if(($controlador != 'usuarios' || $accion != 'login') && ! isset($_SESSION['usuario'])){
    
    echo json_encode([
        'estado' => 401,
        'error' => 'unauthorized',
        'mensaje' => 'No se ha iniciado sesion'
    ]);

    return;
}

// Existe una sesion iniciada
if(isset($_SESSION['usuario'])){

    // Verificacion si la sesion ha expirado
    if(time() >= $_SESSION['tiempo_expiracion']){
        session_destroy();
        
        echo json_encode([
            'estado' => 401,
            'error' => 'unauthorized',
            'mensaje' => 'La sesion ha expirado, inicie sesion nuevamente'
        ]);
    
        return;
    }

    $usuario_logueado = json_decode($_SESSION['usuario'], true);

    // Verificacion si el usuario tiene permisos para realizar la accion
    if(! in_array($usuario_logueado['tipo'], $rutas[$controlador]['acciones'][$accion]['usuarios_permitodos'])){
        echo json_encode([
            'estado' => 403,
            'error' => 'forbidden',
            'mensaje' => 'No tiene permisos para realizar esta accion'
        ]);

        return;
    }
}

// Definicion de constantes
$directorio_actual = preg_split('/(\/|\\\)/', __DIR__);
$directorio_actual = array_pop($directorio_actual);

define('HOST', "http://locahost/$directorio_actual");
define('DURACION_MINUTOS_SESION', 3600);
define('HASH', 'AZrlEz1s53JQx3o');

// Levantamiento de la api
$instancia_controlador = new $rutas[$controlador]['controlador']();

if($rutas[$controlador]['acciones'][$accion]['recibe_datos']){
    $cuerpo_peticion = file_get_contents('php://input');
    $cuerpo_peticion = $cuerpo_peticion != '' ? json_decode(trim($cuerpo_peticion), true) : [];

    echo json_encode($instancia_controlador->{$accion}($cuerpo_peticion));
} else {
    echo json_encode($instancia_controlador->{$accion}());
}
