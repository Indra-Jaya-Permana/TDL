<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'due_date',
        'reminded_at'
    ];

    protected $casts = [
        'due_date' => 'date',
        'reminded_at' => 'datetime'
    ];
}