<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name', 'description'];

    public function requirements()
    {
        return $this->hasMany(Requirement::class);
    }

    public function features()
    {
        return $this->hasMany(ProjectFeature::class);
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    public function engineers()
    {
        return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id');
    }

    public function snapshots()
    {
        return $this->hasMany(ProjectSnapshot::class);
    }
}

