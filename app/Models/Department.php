<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'departments';

    protected $fillable = [
        'number',
        'block',
        'bedrooms',
        'bathrooms',
        'area',
        'status',
    ];
}