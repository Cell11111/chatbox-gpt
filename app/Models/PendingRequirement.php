<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingRequirement extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category',
        'priority',
        'project_id',
        'feature_id',
        'submitted_by',
        'status'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    // ✅ Relationship to Feature
    public function feature()
    {
        return $this->belongsTo(ProjectFeature::class, 'feature_id');
    }

    // ✅ Relationship to User (who submitted it)
    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
