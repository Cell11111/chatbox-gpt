<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProjectReportExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Ensure deterministic ordering so numbering is stable: project.feature.requirement
        $projects = Project::with(['features.requirements', 'features' => function ($q) {
            $q->orderBy('id');
        }, 'features.requirements' => function ($q) {
            $q->orderBy('id');
        }])
            ->orderBy('id')
            ->get();

        return $projects->values()->flatMap(function ($project, $pIndex) {
            // $pIndex is zero-based; numbering is 1-based
            $projectNumber = $pIndex + 1;

            return $project->features->sortBy('id')->values()->flatMap(function ($feature, $fIndex) use ($project, $projectNumber) {
                $featureIndex = $fIndex + 1;

                return $feature->requirements->sortBy('id')->values()->map(function ($requirement, $rIndex) use ($feature, $project, $projectNumber, $featureIndex) {
                    $requirementIndex = $rIndex + 1;
                    $requirementNumber = $projectNumber . '.' . $featureIndex . '.' . $requirementIndex;

                    return [
                        'Project Name' => $project->name,
                        'Project Description' => $project->description,
                        'Category' => $feature->category,
                        'Feature Name' => $feature->feature,
                        'Requirement #' => $requirementNumber,
                        'Requirement Description' => $requirement->description,
                        'Status' => $requirement->status,
                        'Priority' => $requirement->priority,
                        'Author' => $requirement->author->name ?? 'N/A',
                    ];
                });
            });
        });
    }

    public function headings(): array
    {
        return [
            'Project Name',
            'Project Description',
            'Category',
            'Feature Name',
            'Requirement #',
            'Requirement Description',
            'Status',
            'Priority',
            'Author',
        ];
    }
}