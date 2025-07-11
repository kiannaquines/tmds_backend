<?php

namespace App\Http\Controllers;

use App\Models\Thesis;
use App\Models\User;
use App\Models\Adviser;
use App\Models\Panel;
use App\Models\ThesisProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThesisController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'department' => 'required|string',

            'adviser' => 'required|string|exists:users,name|different:panel1|different:panel2|different:panel3',

            'panel1' => [
                'required',
                'string',
                'exists:users,name',
                'different:panel2',
                'different:panel3',
                'different:adviser',
            ],
            'panel2' => [
                'required',
                'string',
                'exists:users,name',
                'different:panel1',
                'different:panel3',
                'different:adviser',
            ],
            'panel3' => [
                'required',
                'string',
                'exists:users,name',
                'different:panel1',
                'different:panel2',
                'different:adviser',
            ],

            'year' => 'required|integer',
            'type' => 'required|string',
        ]);

        $adviser = User::where('name', $validated['adviser'])->first();
        $panel1 = User::where('name', $validated['panel1'])->first();
        $panel2 = User::where('name', $validated['panel2'])->first();
        $panel3 = User::where('name', $validated['panel3'])->first();

        if (!$panel1) return response()->json(['message' => 'First panel cannot be found, please try again.']);
        if (!$panel2) return response()->json(['message' => 'Second panel cannot be found, please try again.']);
        if (!$panel3) return response()->json(['message' => 'Third panel cannot be found, please try again.']);

        if (!$adviser || !$adviser->hasRole('Faculty')) return response()->json(['message' => 'Selected user is not a valid adviser.'], 422);

        $thesis = Thesis::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'adviser' => $adviser['id'],
            'department' => $validated['department'],
            'year' => $validated['year'],
            'type' => $validated['type']
        ]);

        Adviser::create([
            'adviser' => $adviser['id'],
            'study_id' => $thesis->id
        ]);

        Panel::insert([
            [
                'panel' => $panel1->id,
                'study_id' => $thesis->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'panel' => $panel2->id,
                'study_id' => $thesis->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'panel' => $panel3->id,
                'study_id' => $thesis->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        return response()->json([
            'message' => 'Thesis created successfully.',
            'data' => $thesis,
        ], 201);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param integer $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $id)
    {
        $thesis = Thesis::where('user_id', $request->user()->id)->where('id', $id)->first();

        if (!$thesis) return response()->json(['message' => 'Thesis not found.'], 404);


        return response()->json([
            'data' => $thesis,
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param integer $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function notification(Request $request)
    {
        $userId = $request->user()->id;

        $thesisProgress = DB::table('thesis_progress')
            ->join('studies', 'thesis_progress.study_id', '=', 'studies.id')
            ->where('studies.user_id', $userId)
            ->select(
                'thesis_progress.*',
                'studies.title as study_title',
                'studies.type as study_type'
            )
            ->orderByDesc('created_at')->get();

        if ($thesisProgress->isEmpty()) {
            return response()->json(['message' => 'There was no thesis progress found.'], 404);
        }

        return response()->json([
            'data' => $thesisProgress,
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param integer $studyId
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function timeline(Request $request, string $studyId)
    {
        $userId = $request->user()->id;

        $thesisProgress = DB::table('thesis_progress')
            ->join('studies', 'thesis_progress.study_id', '=', 'studies.id')
            ->join('users', 'studies.user_id', '=', 'users.id')
            ->join('users AS check_by_person', 'thesis_progress.check_by', '=', 'check_by_person.id')
            ->where('studies.user_id', $userId)
            ->where('studies.id', $studyId)
            ->select(
                'thesis_progress.*',
                'users.id',
                'users.name',
                'check_by_person.id AS check_by_id',
                'check_by_person.name AS check_by',
                'studies.title as study_title',
                'studies.type as study_type'
            )
            ->orderByDesc('thesis_progress.created_at')->get();

        if ($thesisProgress->isEmpty()) {
            return response()->json(['message' => 'There was no thesis progress found.'], 404);
        }

        return response()->json([
            'data' => $thesisProgress,
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function mySubmissions(Request $request)
    {
        $thesis = Thesis::where('user_id', $request->user()->id)->get();

        if (!$thesis) return response()->json(['message' => 'No thesis found.'], 404);


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
