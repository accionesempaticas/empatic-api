<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $primaryKey = 'status_id';

    protected $fillable = ['status_name'];

    public function participants()
    {
        return $this->hasMany(Participant::class, 'status_id');
    }
}