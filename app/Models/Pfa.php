<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pfa extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rsa_prefix',
        'rsa_digits',
        'is_active',
    ];
}
