<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudyStatus;
use App\Models\ThesisProgress;

class StudyStatusController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $facultyId = $request->user()->id;
        $facultyName = $request->user()->name;

        $validated = $request->validate([
            'status' => 'sometimes|required|in:Pending,In Progress,Complete'
        ]);

        $status = StudyStatus::where('study_id', $id)->where('faculty_id', $facultyId)->first();
        if (!$status) return response()->json(['message' => 'Study status is not found, please try again'], 404);

        $status->update($validated);
        
        ThesisProgress::create([
            'study_id' => $id,
            'check_by' => $facultyId,
            'comment' => $facultyName . ' marked your study as In Progress',
            'status' => 'Approved',
        ]);
        return response()->json(['message' => 'Study status has been updated'], 200);
    }
}
