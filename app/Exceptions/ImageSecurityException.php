<?php

namespace App\Exceptions;

class ImageSecurityException extends ImageSourceException
{
    // Lanzada en caso de detectar intentos de SSRF o protocolos no permitidos
}
