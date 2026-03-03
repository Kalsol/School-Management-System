<?php

namespace App\Models;

use Eloquent;

class Nationality extends Eloquent
{
    //
    public function states()
    {
        return $this->hasMany(State::class);
    }
}
