<?php

namespace App\Exceptions;

use Exception;

class InvalidGoogleTokenException extends Exception
{
    public static function invalid(string $detail = ''): self
    {
        return new self($detail !== '' ? $detail : 'Token Google tidak valid.');
    }
}
