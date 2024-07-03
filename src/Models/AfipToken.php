<?php

namespace Cotein\ApiAfip\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AfipToken extends Model
{
    protected $table = 'afip_tokens';

    public function isActive(): bool
    {
        if (!$this->active) {
            return false;
        }

        // Obtener el tiempo actual en UTC
        $currentTime = Carbon::now();

        // Obtener el tiempo de expiración del token en UTC
        $expirationTime = Carbon::parse($this->expiration_time);

        // Verificar si el tiempo actual es antes del tiempo de expiración
        if ($currentTime->lt($expirationTime)) {
            // El tiempo actual es menor que expirationTime, el token está en el período válido
            return true;
        } else {
            // El tiempo actual no es menor que expirationTime, el token ha expirado
            return false;
        }
    }
}
