<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    protected $fillable = ['title', 'description', 'category', 'priority', 'status', 'project_id', 'feature_id', 'user_id'];

    public function defects()
    {
        return $this->hasMany(Defect::class);
    }

    public function versions()
    {
        return $this->hasMany(RequirementVersion::class)->orderByDesc('version');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function feature()
    {
        return $this->belongsTo(ProjectFeature::class, 'feature_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    
    public function pendingUpdates()
    {
        return $this->hasMany(PendingUpdate::class, 'requirement_id');
    }


}

