<?php

namespace Cotein\ApiAfip\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AfipToken extends Model
{
    protected $table = 'afip_tokens';

    public function isValidDate(): bool
    {
        $date =  new Carbon();

        $expirationTime = $date->parse($this->expiration_time);

        $toDay = $date->parse($date->now());

        if (strtotime($toDay) >= strtotime($expirationTime)) {
            return true; //inválido
        }

        return false;
    }

    public function isActive(): bool
    {

        if ($this->active && !$this->isValidDate()) {
            return true;
        }

        return false;
    }
}
