<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'author_id', 'project_id'];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
