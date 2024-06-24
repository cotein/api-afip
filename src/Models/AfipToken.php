<?php

namespace Cotein\ApiAfip\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AfipToken extends Model
{
    protected $table = 'afip_tokens';

    public function isActive(): bool
    {
        /* $date =  new Carbon();

        $expirationTime = $date->parse($this->expiration_time);

        $toDay = $date->parse($date->now());

        if (strtotime($toDay) >= strtotime($expirationTime)) {
            return true; //inválido
        }

        return false; */
        // Check if the token is active
        if (!$this->active) {
            return false;
        }

        // Get the token expiration time in UTC
        $generationTime = Carbon::parse($this->generation_time);
        $expirationTime = Carbon::parse($this->expiration_time);

        // Check if the current time is before the expiration time
        if ($generationTime->lt($expirationTime)) {
            // generationTime es menor que expirationTime, el token está en el período válido
            return true;
        } else {
            // generationTime no es menor que expirationTime, el token no está en el período válido
            return false;
        }
    }

    /*  public function isActive(): bool
    {

        if ($this->active && !$this->isValidDate()) {
            return true;
        }

        return false;
    } */
}
