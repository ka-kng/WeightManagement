<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'birth_date',
        'height',
        'target_weight',
        'email_verified_at',
    ];

    public function record()
    {
        return $this->hasMany(Record::class);
    }

    protected $casts = [
        'birth_date' => 'date',
    ];

}
