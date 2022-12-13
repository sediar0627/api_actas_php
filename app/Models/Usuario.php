<?php

namespace App\Models;

use App\Config\BD;
use App\Enums\TipoUsuarioEnum;
use App\Config\Correo;

class Usuario extends BD
{
    public int $id;
    public string $correo;
    public bool $correo_verificado;
    public string $username;
    public string $nombres;
    public string $apellidos;
    public TipoUsuarioEnum $tipo;

    private string $password;
    private string $token;

    public function __construct(int $id, string $correo, bool $correo_verificado, string $username, string $password, string $nombres, string $apellidos, TipoUsuarioEnum $tipo, string $token)
    {
        $this->id = $id;
        $this->correo = $correo;
        $this->correo_verificado = $correo_verificado;
        $this->username = $username;
        $this->password = $password;
        $this->nombres = $nombres;
        $this->apellidos = $apellidos;
        $this->tipo = $tipo;
        $this->token = $token;
    }

    public function validarPassword(string $password): bool
    {
        return password_verify(HASH.$password, $this->password);
    }

    public function validarVerificacionCorreo(string $token): bool
    {
        return password_verify(HASH.$token, $this->token);
    }

    public function cambiarPassword(string $password)
    {
        $hash = password_hash(HASH.$password, PASSWORD_BCRYPT);

        $conexion = self::conexion();
        $sql = "UPDATE usuarios SET password = '$hash' WHERE id = {$this->id};";
        $resultado = self::consulta($conexion, $sql);
        self::desconectar($conexion);
        
        return $resultado;
    }

    public function asignarToken($numero)
    {
        $hash = password_hash(HASH.$numero, PASSWORD_BCRYPT);

        $conexion = self::conexion();
        $sql = "UPDATE usuarios SET token = '$hash' WHERE id = {$this->id};";
        $resultado = self::consulta($conexion, $sql);
        self::desconectar($conexion);
        
        return $resultado;
    }

    public function validarCorreo(string $token)
    {
        if(! $this->validarVerificacionCorreo($token)){
            return false;
        }

        $conexion = self::conexion();
        $sql = "UPDATE usuarios SET correo_verificado = 1 WHERE id = {$this->id};";
        self::consulta($conexion, $sql);
        self::desconectar($conexion);
        
        return true;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'correo' => $this->correo,
            'correo_verificado' => $this->correo_verificado,
            'username' => $this->username,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'tipo' => $this->tipo,
            'token' => $this->token,
        ];
    }

    public function toArrayAll()
    {
        return array_merge($this->toArray(), [
            'password' => $this->password,
            'token' => $this->token,
        ]);
    }

    public function recargar()
    {
        $conexion = self::conexion();
        $sql = "SELECT * FROM usuarios WHERE id = {$this->id};";
        $usuario_bd = self::consultaRetorno($conexion, $sql);
        self::desconectar($conexion);

        if (! $usuario_bd) {
            throw new \Exception("No se encontró el usuario con id {$this->id}");
        }

        $this->correo = $usuario_bd['correo'];
        $this->correo_verificado = $usuario_bd['correo_verificado'];
        $this->username = $usuario_bd['username'];
        $this->password = $usuario_bd['password'];
        $this->nombres = $usuario_bd['nombres'];
        $this->apellidos = $usuario_bd['apellidos'];
        $this->tipo = TipoUsuarioEnum::from($usuario_bd['tipo']);
        $this->token = $usuario_bd['token'];
    }

    public function actualizar($data)
    {
        $conexion = self::conexion();

        $sql = "UPDATE usuarios SET 
            correo = '{$data['correo']}', 
            username = '{$data['username']}', 
            nombres = '{$data['nombres']}', 
            apellidos = '{$data['apellidos']}', 
            tipo = '{$data['tipo']}'
        ";

        $password = isset($data['password']) ? $data['password'] : '';
        $password = str_replace('*', '', $password);

        if($password !== '') {
            $password = str_starts_with($password, '$2y$') ? $password : password_hash(HASH.$password, PASSWORD_BCRYPT);
            $sql .= ", password = '{$password}'";
        }

        $sql .= " WHERE id = {$this->id};";

        $resultado = self::consulta($conexion, $sql);

        self::desconectar($conexion);

        return $resultado;
    }

    public function eliminar()
    {
        $conexion = self::conexion();
        $sql = "DELETE FROM usuarios WHERE id = {$this->id};";
        $resultado = self::consulta($conexion, $sql);
        self::desconectar($conexion);

        return $resultado;
    }

    public static function crearInstancia(array $usuario_bd): Usuario
    {
        return new Usuario(
            $usuario_bd['id'],
            $usuario_bd['correo'],
            $usuario_bd['correo_verificado'] == 1,
            $usuario_bd['username'],
            $usuario_bd['password'],
            $usuario_bd['nombres'],
            $usuario_bd['apellidos'],
            TipoUsuarioEnum::from($usuario_bd['tipo']),
            $usuario_bd['token'] ?? ''
        );
    }

    public static function usuarioLogueado(): ?Usuario
    {
        return isset($_SESSION['usuario']) ? self::crearInstancia(json_decode($_SESSION['usuario'], true)) : null;
    }

    public static function listarTodos($conexionAnterior = null): array
    {
        $conexion = $conexionAnterior ?? self::conexion();

        $usuario_logueado = self::usuarioLogueado();

        $sql = "SELECT * FROM usuarios WHERE id != {$usuario_logueado->id};";
        
        $resultado = self::consulta($conexion, $sql);
        
        $usuarios = [];

        while ($usuario_bd = $resultado->fetch_assoc()) {
            $usuarios[] = self::crearInstancia($usuario_bd);
        }

        if (! $conexionAnterior) {
            self::desconectar($conexion);
        }

        return $usuarios;
    }

    public static function buscar($busqueda, $conexionAnterior = null): ?Usuario
    {
        $conexion = $conexionAnterior ?? self::conexion();

        $sql = "SELECT * FROM usuarios WHERE";
        
        $usuario_logueado = self::usuarioLogueado();

        if($usuario_logueado){
            $sql .= " id != {$usuario_logueado->id} AND";
        }

        if(is_numeric($busqueda)) {
            $sql .= " id = $busqueda;";
        } else {
            $sql .= " (LOWER(username) = '$busqueda' OR LOWER(correo) = '$busqueda');";
        }

        $usuario_bd = self::consultaRetorno($conexion, $sql);

        if ($usuario_bd) {
            $usuario = self::crearInstancia($usuario_bd);
            
            if (! $conexionAnterior) {
                self::desconectar($conexion);
            }

            return $usuario;
        }

        if (! $conexionAnterior) {
            self::desconectar($conexion);
        }

        return null;
    }

    public static function crear($data): ?Usuario
    {
        $conexion = self::conexion();

        $data['password'] = password_hash(HASH.$data['password'], PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuarios 
            (correo, username, password, nombres, apellidos, tipo) 
            VALUES (
                '{$data['correo']}', 
                '{$data['username']}', 
                '{$data['password']}', 
                '{$data['nombres']}', 
                '{$data['apellidos']}', 
                '{$data['tipo']}'
            );
        ";

        self::consulta($conexion, $sql);

        $usuario = self::buscar($conexion->insert_id, $conexion);

        self::desconectar($conexion);

        return $usuario;
    }

    public static function login(string $usuario_o_correo, string $password): ?Usuario
    {
        $usuario = self::buscar($usuario_o_correo);
        if ($usuario && $usuario->validarPassword($password)) {
            return $usuario;
        }
        return null;
    }
}
