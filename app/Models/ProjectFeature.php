<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFeature extends Model
{
    protected $fillable = [
        'project_id',
        'category',
        'feature',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function requirements()
    {
        return $this->hasMany(Requirement::class, 'feature_id');
    }
}
