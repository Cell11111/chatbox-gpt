<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingResolution extends Model
{
    use HasFactory;

    protected $table = 'pending_resolution'; // Specify the table name
    protected $fillable = [
        'conflict_id',
        'requirement_a_id',
        'requirement_b_id',
        'resolution',
        'updated_data',
        'proposed_by',
        'comments',
        'status',
        'reviewed_at',
        'reviewed_by',
    ];

    // Relationships
    public function conflict()
    {
        return $this->belongsTo(Conflict::class);
    }

    public function requirementA()
    {
        return $this->belongsTo(Requirement::class, 'requirement_a_id');
    }

    public function requirementB()
    {
        return $this->belongsTo(Requirement::class, 'requirement_b_id');
    }

    public function proposedBy()
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }
}