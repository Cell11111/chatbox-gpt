<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectSnapshot extends Model
{
    protected $fillable = ['project_id', 'snapshot_name', 'snapshot_data', 'created_by', 'description'];

    protected $casts = [
        'snapshot_data' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
