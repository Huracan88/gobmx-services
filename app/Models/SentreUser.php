<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentreUser extends Model
{
    use HasFactory;

    protected $fillable = ['username', 'password'];

    public function records()
    {
        return $this->hasMany(SentreRecord::class);
    }
}
