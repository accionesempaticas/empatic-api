<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SignedDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_template_id',
        'signed_at',
        'signature_data',
        'ip_address',
        'digital_signature_id',
        'signed_pdf_path',
        'signature_metadata'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documentTemplate()
    {
        return $this->belongsTo(DocumentTemplate::class);
    }
}
