<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    

    protected $fillable = ['region', 'province', 'address'];

    public function persons()
    {
        return $this->hasMany(Person::class, 'location_id');
    }
}
