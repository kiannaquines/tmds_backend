<?php

namespace App\Http\Controllers;

use App\Models\Thesis;
use App\Models\User;
use Illuminate\Http\Request;

class ThesisController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|unique:studies,title',
            'adviser' => 'required|integer|exists:users,id',
            'department' => 'required|string',
            'year' => 'required|integer',
            'type' => 'required|string',
        ]);

        $adviser = User::find($validated['adviser']);
        
        if (!$adviser || !$adviser->hasRole('Adviser')) return response()->json(['message' => 'Selected user is not a valid adviser.'], 422);

        $validated['user_id'] = $request->user()->id;

        $thesis = Thesis::create($validated);

        return response()->json([
            'message' => 'Thesis created successfully.',
            'data' => $thesis,
        ], 201);
    }

    /**
     * @param integer $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $thesis = Thesis::find($id);

        if (!$thesis) return response()->json(['message' => 'Thesis not found.'], 404);


        return response()->json([
            'data' => $thesis,
        ]);
    }

    /**
     * @param integer $id
     * @param \Illuminate\Http\Request $request,
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $thesis = Thesis::find($id);

        if (!$thesis) return response()->json(['message' => 'Thesis not found.'], 404);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|unique:studies,title,' . $id,
            'adviser' => 'sometimes|required|integer|exists:users,id',
            'department' => 'sometimes|required|string',
            'year' => 'sometimes|required|integer',
            'type' => 'sometimes|required|string',
        ]);

        if (isset($validated['adviser'])) {
            $adviser = User::find($validated['adviser']);
            if (!$adviser || !$adviser->hasRole('Adviser')) return response()->json(['message' => 'Selected user is not a valid adviser.'], 422);
        }

        $thesis->update($validated);

        return response()->json([
            'message' => 'Thesis updated successfully.',
            'data' => $thesis,
        ]);
    }

    /**
     * @param integer $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $thesis = Thesis::find($id);

        if (!$thesis) return response()->json(['message' => 'Thesis not found.'], 404);

        $thesis->delete();

        return response()->json([
            'message' => 'Thesis deleted successfully.',
        ]);
    }
}
