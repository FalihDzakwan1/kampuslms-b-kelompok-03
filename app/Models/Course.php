<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'code', 
        'name', 
        'description', 
        'sks', 
        'lecturer_id',
        'status'
        ];
    public function lecturer()
    {
        return $this->belongsTo(
            User::class,
            'lecturer_id'
        );
    }


    public function students()
    {
        return $this->belongsToMany(
            User::class
        )->withTimestamps();
    }


    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }   
}
