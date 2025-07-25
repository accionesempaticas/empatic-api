<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $table = 'people';

    protected $fillable = [
        'dni', 'first_name', 'last_name', 'full_name', 'gender',
        'phone_number', 'email', 'date_of_birth', 'age',
        'nationality', 'family_phone_number', 'linkedin',
        'location_id', 'formation_id', 'experience_id'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function formation()
    {
        return $this->belongsTo(AcademicFormation::class, 'formation_id');
    }

    public function experience()
    {
        return $this->belongsTo(Experience::class);
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }
}
