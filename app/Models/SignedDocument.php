<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SignedDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'document_type',
        'file_path',
        'signature_data',
        'signed_at'
    ];

    protected $casts = [
        'signed_at' => 'datetime'
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
