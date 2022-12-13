<?php

$rutas = [
    'usuarios' => [
        'controlador' => App\Controllers\UsuarioController::class,
        'acciones' => [
            'login' => [
                'recibe_datos' => true,
                'usuarios_permitodos' => [
                    App\Enums\TipoUsuarioEnum::Administrador->value,
                    App\Enums\TipoUsuarioEnum::Informes->value
                ],
            ],
            'listarUsuarios' => [
                'recibe_datos' => false,
                'usuarios_permitodos' => [
                    App\Enums\TipoUsuarioEnum::Administrador->value
                ],
            ],
            'verificarCorreo' => [
                'recibe_datos' => true,
                'usuarios_permitodos' => [
                    App\Enums\TipoUsuarioEnum::Administrador->value,
                    App\Enums\TipoUsuarioEnum::Informes->value
                ],
            ],
            'enviarCorreoVerificacion' => [
                'recibe_datos' => false,
                'usuarios_permitodos' => [
                    App\Enums\TipoUsuarioEnum::Administrador->value,
                    App\Enums\TipoUsuarioEnum::Informes->value
                ],
            ],
        ]
    ]
];