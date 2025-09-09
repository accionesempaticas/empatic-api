<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Program extends Model
{
    use HasApiTokens, Notifiable, HasFactory;
    protected $fillable = ['name', 'description'];

    public function persons()
    {
        return $this->hasMany(Person::class, 'location_id');
    }

}
