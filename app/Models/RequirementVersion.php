<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequirementVersion extends Model
{
    protected $fillable = ['requirement_id', 'version', 'title', 'description', 'category', 'priority', 'status', 'project_id', 'feature_id', 'author_id',];

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    public function feature()
    {
        return $this->belongsTo(ProjectFeature::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
