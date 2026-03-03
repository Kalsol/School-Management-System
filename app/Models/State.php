<?php

namespace App\Models;

use Eloquent;

class State extends Eloquent
{
    public function ministry()
    {
       // return $this->hasMany(Ministry::class);
    }
    public function nationality()
    {
        return $this->belongsTo(Nationality::class);
    }
    public function lgas()
    {
        return $this->hasMany(Lga::class);
    }
}
