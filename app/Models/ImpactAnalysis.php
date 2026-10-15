<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImpactAnalysis extends Model
{
    protected $fillable = ['requirement_id', 'field', 'old', 'new', 'impact', 'recommendation', 'other_impacted_requirements',];
}
