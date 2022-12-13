<?php

namespace App\Config;

use PHPMailer\PHPMailer\PHPMailer;
use App\Models\Usuario;

class Correo
{
    private const HOST = 'smtp.gmail.com';
    private const USER = 'corprugm@gmail.com';
    private const PASS = 'pszuqefofcgifwun';
    private const PORT = 587;

    public static function enviar($destinatario, $asunto, $html): bool
    {
        try {
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Host = self::HOST;
            $mail->Port = self::PORT;
            $mail->Username = self::USER;
            $mail->Password = self::PASS;

            $mail->setFrom(self::USER, 'PROYECTO DE ACTAS');
            $mail->addAddress($destinatario);
            $mail->Subject = $asunto;
            $mail->Body = $html;
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            return $mail->send();
        } catch (\Throwable $th) {
            return false;
        }
    }

    public static function correoVerificacion(Usuario $usuario, int $codigo)
    {
        $html = file_get_contents(__DIR__.'/../Emails/CorreoVerificacion.html');
        $html = str_replace('__nombre__', $usuario->nombres . ' '. $usuario->apellidos, $html);
        $html = str_replace('__codigo__', $codigo, $html);

        return self::enviar($usuario->correo, 'Verificacion de usuario', $html);
    }
    
}