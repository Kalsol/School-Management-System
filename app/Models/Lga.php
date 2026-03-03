<?php

namespace App\Models;

use Eloquent;

class Lga extends Eloquent
{
    public function ministry()
    {
       // return $this->hasMany(Ministry::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }
    
}
