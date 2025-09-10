<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'weight',
        'sleep_hours',
        'sleep_minutes',
        'meals',
        'meal_detail',
        'meal_photos',
        'exercises',
        'exercise_detail',
    ];

    protected $casts = [
        'meals' => 'array',
        'meal_photos' => 'array',
        'exercises' => 'array',
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
