<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{

    protected $fillable = ['experience_time', 'other_volunteer_work'];

    public function persons()
    {
        return $this->hasMany(Person::class, 'experience_id');
    }
}