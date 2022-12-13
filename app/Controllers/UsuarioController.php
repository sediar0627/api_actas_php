<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Traits\ControllersTrait;
use App\Config\Correo;

class UsuarioController
{
    use ControllersTrait;

    public function login($data)
    {
        $usuario_logueado = Usuario::usuarioLogueado();

        if($usuario_logueado){
            session_unset();
        }

        $reglas = [
            'required' => ['usuario_o_correo', 'password'],
        ];

        $validacion = $this->validar($data, $reglas);

        if($validacion['status'] === false) {
            return [
                'estado' => 422,
                'errores' => $validacion['errores']
            ];
        }

        $usuario = Usuario::login($data['usuario_o_correo'], $data['password']);

        if($usuario === null) {
            return [
                'estado' => 404,
                'errores' => [
                    'usuario_o_correo' => ['Datos incorrectos'],
                ]
            ];
        }

        if(! $usuario->correo_verificado){
            $numero_aleatorio = rand(100000, 999999);
            $usuario->asignarToken(strval($numero_aleatorio));

            Correo::correoVerificacion($usuario, $numero_aleatorio);

            $usuario->recargar();
        }

        $_SESSION['usuario'] = json_encode($usuario->toArrayAll());
        $_SESSION['tiempo_expiracion'] = time() + DURACION_MINUTOS_SESION;

        return [
            'estado' => 200,
            'usuario' => $usuario->toArray(),
            'expiracion' => date('Y-m-d H:i:s', $_SESSION['tiempo_expiracion'])
        ];
    }

    public function listarUsuarios()
    {
        return [
            'estado' => 200,
            'usuarios' => array_map(fn($usuario) => $usuario->toArray(), Usuario::listarTodos())
        ];
    }

    public function verificarCorreo($data)
    {
        $reglas = [
            'required' => ['codigo_verificacion'],
        ];

        $validacion = $this->validar($data, $reglas);

        if($validacion['status'] === false) {
            return [
                'estado' => 422,
                'errores' => $validacion['errores']
            ];
        }

        $usuario_logueado = Usuario::usuarioLogueado();

        if(! $usuario_logueado->validarCorreo($data['codigo_verificacion'])){
            return [
                'estado' => 404,
                'errores' => [
                    'codigo_verificacion' => ['Codigo incorrecto'],
                ]
            ];
        }

        $usuario_logueado->recargar();

        $_SESSION['usuario'] = json_encode($usuario_logueado->toArrayAll());

        return [
            'estado' => 200,
            'usuario' => $usuario_logueado->toArray(),
            'mensaje' => 'Correo verificado'
        ];
    }

    function enviarCorreoVerificacion()
    {
        $usuario_logueado = Usuario::usuarioLogueado();

        $numero_aleatorio = rand(100000, 999999);
        $usuario_logueado->asignarToken(strval($numero_aleatorio));

        Correo::correoVerificacion($usuario_logueado, $numero_aleatorio);

        $usuario_logueado->recargar();

        $_SESSION['usuario'] = json_encode($usuario_logueado->toArrayAll());

        return [
            'estado' => 200,
            'mensaje' => 'Correo enviado'
        ];
    }
}