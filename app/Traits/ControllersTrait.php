<?php

namespace App\Traits;

use Valitron\Validator;

Validator::langDir(__DIR__ . "/../../vendor/vlucas/valitron/lang");
Validator::lang('es');

trait ControllersTrait
{
    public static function validar($data, $reglas): array
    {
        $validacion = new Validator($data);

        foreach ($reglas as $tipo_regla => $campos) {
            $validacion->rule($tipo_regla, $campos);
        }

        if($validacion->validate()) {
            return ['status' => true];
        }

        return [
            'status' => false,
            'errores' => $validacion->errors()
        ];
    }
}