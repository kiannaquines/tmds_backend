<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudyStatus;

class StudyStatusController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => 'sometimes|required|in:Pending,In Progress,Complete'
        ]);

        $status = StudyStatus::find($id);

        if (!$status) return response()->json(['message' => 'Study status is not found, please try again'], 404);

        $status->update($validated);
        return response()->json(['message' => 'Study status has been updated'], 200);
    }
}
