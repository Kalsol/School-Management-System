<?php

namespace App\Models;

use App\User;
use Eloquent;
use Spatie\Activitylog\LogOptions;

class Notice extends Eloquent
{
    // use LogsActivity;

    protected $fillable = ['from_id', 'editor_id', 'viewers_ids', 'title', 'body'];

    public function user()
    {
        return $this->belongsTo(User::class, "from_id");
    }

    public function editor()
    {
        return $this->belongsTo(User::class, "editor_id");
    }
}
