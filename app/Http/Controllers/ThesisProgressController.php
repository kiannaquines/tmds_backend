<?php

namespace App\Http\Controllers;

use App\Models\ThesisProgress;
use Illuminate\Http\Request;

class ThesisProgressController extends Controller
{

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'study_id' => 'required|integer|exists:studies,id',
            'comment' => 'required|string|max:500',
            'status' => 'required|string|in:Revise,Approved',
        ]);
        $validated['check_by'] = $request->user()->id;
        ThesisProgress::create($validated);
        return response()->json(['message' => 'You have successfully added the thesis progress.'], 201);
    }

    /**
     * @param string $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $thesis = ThesisProgress::where('id', '=', $id)->get();
        if (!$thesis) return response()->json(['message' => 'Thesis not found, please try again.'], 404);
        return response()->json(['thesis' => $thesis], 200);
    }

    /**
     * @param string $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function showAllProgress(string $id)
    {
        $progress = ThesisProgress::where('study_id', '=', $id)->get();
        if (!$progress) return response()->json(['message' => 'Thesis progress information not found, please try again.'], 404);
        return response()->json(['progress' => $progress], 200);
    }

    /**
     * @param string $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $progress = ThesisProgress::where('id', '=', $id);
        if (!$progress) return response()->json(['message' => 'Information not found, please try again.'], 404);
        $progress->delete();
        return response()->json(['message' => 'You have successfully removed the progress information'], 200);
    }
}
