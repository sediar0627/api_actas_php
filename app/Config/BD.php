<?php

namespace App\Config;

use Exception;
use mysqli;

abstract class BD
{
    private const HOST = 'localhost';
    private const USUARIO = 'root';
    private const PASS = '';
    private const BASEDATOS = 'api_actas';

    public static function conexion()
    {
        $conexion = new mysqli(self::HOST, self::USUARIO, self::PASS, self::BASEDATOS);

        if ($conexion->connect_errno) {
            throw new Exception('Error de conexión a la base de datos: ' . $conexion->connect_error);
        }

        return $conexion;
    }

    public static function consulta($conexion, $sql)
    {
        if(! $conexion){
            throw new Exception('No hay conexión a la base de datos');
        }

        $resultado = $conexion->query($sql);
        return $resultado;
    }

    public static function consultaSimple($conexion, $sql)
    {
        if(! $conexion){
            throw new Exception('No hay conexión a la base de datos');
        }

        $conexion->query($sql);
    }

    public static function consultaRetorno($conexion, $sql)
    {
        if(! $conexion){
            throw new Exception('No hay conexión a la base de datos');
        }

        $resultado = $conexion->query($sql);
        $row = $resultado->fetch_assoc();
        return $row;
    }

    public static function desconectar($conexion)
    {
        if(! $conexion){
            throw new Exception('No hay conexión a la base de datos');
        }

        $conexion->close();
    }
}