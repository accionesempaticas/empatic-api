<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicFormation extends Model
{

    protected $fillable = ['academic_degree', 'career', 'formation_center'];

    public function persons()
    {
        return $this->hasMany(Person::class, 'formation_id');
    }
}