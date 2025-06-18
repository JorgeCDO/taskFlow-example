<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $table = 'tasks';
    protected $fillable = [
        'title',
        'description',
        'status',
        'expiration_date',
        'user_id',
    ];

    public function logs()
    {
        return $this->hasMany(TaskLog::class);
    }

    public $timestamps = true;
}
