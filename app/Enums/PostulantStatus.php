<?php

namespace App\Enums;

enum PostulantStatus: string
{
    case REGISTERED = 'REGISTERED';
    case INTERVIEWING = 'INTERVIEWING';
    case DOCS_PENDING = 'DOCS_PENDING';
    case COMPLETED = 'COMPLETED';
    case ACCEPTED = 'ACCEPTED';
    case REJECTED = 'REJECTED';
    case CANCELLED = 'CANCELLED';
}
