<?php

namespace App\Enums;

/**
 * Tipos de usuario en el sistema
 */
enum TipoUsuarioEnum : int
{
    case Administrador = 1;
    case Informes = 2;

    public function description(): string
    {
        return match ($this) {
            TipoUsuarioEnum::Administrador => 'Administrador',
            TipoUsuarioEnum::Informes => 'Informes',
        };
    }
}
