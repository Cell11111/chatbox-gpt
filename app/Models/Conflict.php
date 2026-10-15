<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conflict extends Model
{
    use HasFactory;

    protected $fillable = [
        'requirement_a_id',
        'requirement_b_id',
        'summary',
        'severity',
        'status',
        'project_id',
        'resolution',
        'comments', // Add project_id
    ];

    // Define relationships
    public function requirementA()
    {
        return $this->belongsTo(Requirement::class, 'requirement_a_id');
    }

    public function requirementB()
    {
        return $this->belongsTo(Requirement::class, 'requirement_b_id');
    }
        public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
