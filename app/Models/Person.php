<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Person extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;
    protected $table = 'people';

    protected $fillable = [
        'document_type', 'document_number', 'first_name', 'last_name', 'full_name', 'gender',
        'phone_number', 'email', 'date_of_birth', 'age',
        'nationality', 'family_phone_number', 'linkedin',
        'location_id', 'formation_id', 'experience_id',
        'cv_path', // <-- se agrega esto
        'password', 'role', 'status', 'reject_reason'
    ];

    protected $hidden = [
        'password',
        'remember_token',
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

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
