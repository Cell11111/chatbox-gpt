<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Defect extends Model
{
        protected $fillable = [
        'requirement_id',
        'description',
        'type',
        'suggestion',
    ];

    public function requirement()
{
    return $this->belongsTo(Requirement::class);
}
}
