<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'requirement_id',
        'project_id',
        'updated_data',
        'previous_data',
        'submitted_by',
        'status',
        'reviewed_by',
    ];

    // Relationships
    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
