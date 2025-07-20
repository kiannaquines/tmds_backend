<?php

namespace App\Http\Controllers;

use App\Models\Thesis;
use App\Models\User;
use App\Models\Adviser;
use App\Models\Panel;
use App\Models\ThesisProgress;
use App\Models\StudyStatus;
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
            'type' => 'required|string|in:Outline,Manuscript',
        ]);

        $studentId = $request->user()->id;
        $normalizedTitle = strtolower(trim($validated['title']));
        $type = $validated['type'];

        $existingTypes = Thesis::where('user_id', $studentId)
            ->whereRaw('LOWER(TRIM(title)) = ?', [$normalizedTitle])
            ->pluck('type')
            ->map(fn($t) => strtolower($t))
            ->toArray();

        if (in_array(strtolower($type), $existingTypes)) {
            return response()->json([
                'message' => "You already submitted a $type for this study."
            ]);
        }

        if (count($existingTypes) >= 2) {
            return response()->json([
                'message' => 'You can only submit an outline and manuscript per study title.'
            ]);
        }

        $adviser = User::where('name', $validated['adviser'])->first();
        $panel1 = User::where('name', $validated['panel1'])->first();
        $panel2 = User::where('name', $validated['panel2'])->first();
        $panel3 = User::where('name', $validated['panel3'])->first();

        if (!$panel1 || !$panel2 || !$panel3) {
            return response()->json([
                'message' => 'One or more panel members could not be found.'
            ]);
        }

        $drc = User::role('Department Research Coordinator')->first();
        $crc = User::role('College Research Coordinator')->first();
        $dean = User::role('College Dean')->first();
        $dc = User::role('Department Chairperson')->first();

        $thesis = Thesis::create([
            'user_id' => $studentId,
            'title' => $validated['title'],
            'adviser' => $adviser->id,
            'department' => $validated['department'],
            'year' => $validated['year'],
            'type' => $type,
        ]);

        Adviser::create([
            'adviser' => $adviser->id,
            'study_id' => $thesis->id,
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
            ],
        ]);

        $facultyIds = array_filter([
            $adviser?->id,
            $panel1?->id,
            $panel2?->id,
            $panel3?->id,
            $drc?->id,
            $crc?->id,
            $dean?->id,
            $dc?->id,
        ]);

        $statuses = array_map(function ($facultyId) use ($studentId, $thesis) {
            return [
                'student_id' => $studentId,
                'faculty_id' => $facultyId,
                'study_id' => $thesis->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $facultyIds);

        StudyStatus::insert($statuses);

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

        $progress = ThesisProgress::with([
            'study.user',
            'checkedBy.roles'
        ])
            ->whereHas('study', function ($q) use ($userId, $studyId) {
                $q->where('user_id', $userId)->where('id', $studyId);
            })
            ->orderByDesc('created_at')
            ->get();

        if ($progress->isEmpty()) {
            return response()->json(['message' => 'There was no thesis progress found.'], 404);
        }

        $formatted = $progress->map(function ($item) {
            return [
                'id' => $item->id,
                'study_id' => $item->study_id,
                'comment' => $item->comment,
                'status' => $item->status,
                'created_at' => $item->created_at,
                'checked_by' => $item->checkedBy?->name,
                'checked_by_roles' => $item->checkedBy?->roles->pluck('name')->implode('/'),
                'study_title' => $item->study->title ?? null,
                'study_type' => $item->study->type ?? null,
            ];
        });

        return response()->json(['data' => $formatted]);
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
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function myAdvisees(Request $request)
    {
        $adviserId = $request->user()->id;

        $adviseesList = DB::table('advisers')
            ->join('studies', 'advisers.study_id', '=', 'studies.id')
            ->join('users', 'studies.user_id', '=', 'users.id')
            ->where('advisers.adviser', '=', $adviserId)
            ->select(
                'users.id as student_id',
                'users.name as student_name',
                'studies.title as study_title',
                'studies.type as study_type'
            )
            ->get();

        if ($adviseesList->isEmpty()) {
            return response()->json(['message' => 'No advisee data found.'], 404);
        }

        return response()->json([
            'data' => $adviseesList,
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

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function studiesBelongsToMe(Request $request)
    {
        $userId = $request->user()->id;
        $thesisBelongToMe = Thesis::with(['user', 'adviser'])->where('adviser', $userId)->get();

        if ($thesisBelongToMe->isEmpty()) {
            return response()->json(['message' => 'No thesis advisee found.'], 404);
        }

        return response()->json([
            'data' => $thesisBelongToMe,
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function includedMeAsPanel(Request $request)
    {
        $userId = $request->user()->id;
        $thesisBelongToMe = Panel::with(['user', 'adviser'])->where('adviser', $userId)->get();

        if ($thesisBelongToMe->isEmpty()) {
            return response()->json(['message' => 'No thesis advisee found.'], 404);
        }

        return response()->json([
            'data' => $thesisBelongToMe,
        ]);
    }



    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function manuscript(Request $request)
    {
        $adviserId = $request->user()->id;
        $thesis = Thesis::where('type', 'Manuscript')->where('adviser', $adviserId)->get();

        if (!$thesis) return response()->json(['message' => 'No thesis found.'], 404);


        return response()->json([
            'data' => $thesis,
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function outline(Request $request)
    {
        $adviserId = $request->user()->id;
        $thesis = Thesis::where('type', 'Outline')->where('adviser', $adviserId)->get();

        if (!$thesis) return response()->json(['message' => 'No thesis found.'], 404);


        return response()->json([
            'data' => $thesis,
        ]);
    }


    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function myAdviser(Request $request)
    {
        $studentId = $request->user()->id;

        $adviserList = DB::table('users')
            ->join('advisers', 'users.id', '=', 'advisers.adviser')
            ->join('studies', 'advisers.study_id', '=', 'studies.id')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('studies.user_id', '=', $studentId)
            ->select(
                'users.id as adviser_id',
                'users.name as adviser_name',
                'users.email as adviser_email',
                'roles.name as role'
            )
            ->get();

        if ($adviserList->isEmpty()) {
            return response()->json(['message' => 'No adviser data found.'], 404);
        }

        return response()->json([
            'data' => $adviserList,
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function myPanels(Request $request)
    {
        $studentId = $request->user()->id;

        $panelList = DB::table('users')
            ->join('panels', 'users.id', '=', 'panels.panel')
            ->join('studies', 'panels.study_id', '=', 'studies.id')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('studies.user_id', '=', $studentId)
            ->select(
                'users.id as panel_id',
                'users.name as panel_name',
                'users.email as panel_email',
                'roles.name as role'
            )
            ->get();

        if ($panelList->isEmpty()) {
            return response()->json(['message' => 'No panel data found.'], 404);
        }

        return response()->json([
            'data' => $panelList,
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function isAlreadyCheckedAndApproved(Request $request, string $studyId)
    {
        try {
            $userId = $request->user()->id;

            $exists = ThesisProgress::where('check_by', $userId)
                ->where('study_id', $studyId)
                ->where('status', 'Approved')
                ->exists();

            return response()->json([
                'already_approved' => $exists,
                'message' => $exists ? 'This thesis is already approved.' : 'No prior approval found.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to check approval status',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getAllStudentIncludedMeAsAPanel(Request $request)
    {
        try {
            $panelId = $request->user()->id;

            $students = DB::table('panels')
                ->join('studies', 'panels.study_id', '=', 'studies.id')
                ->join('users', 'studies.user_id', '=', 'users.id')
                ->where('panels.panel', $panelId)
                ->select(
                    'users.name',
                    'users.email',
                    'studies.title',
                    'studies.type'
                )
                ->get();

            return response()->json(['data' => $students]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student list',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllPendingStatus(Request $request)
    {
        $facultyId = $request->user()->id;

        $pendingStatus = StudyStatus::with(['student', 'faculty', 'study'])
            ->where('status', 'Pending')
            ->where('faculty_id', $facultyId)
            ->get()
            ->map(function ($row) {
                $faculty = $row->faculty;

                return [
                    'id' => $row->id,
                    'student' => $row->student,
                    'study' => $row->study,
                    'status' => $row->status,
                    'faculty' => $faculty,
                    'faculty_role' => $faculty && method_exists($faculty, 'getRoleNames')
                        ? $faculty->getRoleNames()->first()
                        : null,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];
            });

        if ($pendingStatus->isEmpty()) {
            return response()->json([], 404);
        }

        return response()->json(['data' => $pendingStatus]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllInprogressStatus(Request $request)
    {
        $facultyId = $request->user()->id;

        $inProgressStatus = StudyStatus::with(['student', 'faculty', 'study'])
            ->where('status', 'In Progress')
            ->where('faculty_id', $facultyId)
            ->get()
            ->map(function ($row) {
                $faculty = $row->faculty;

                return [
                    'id' => $row->id,
                    'student' => $row->student,
                    'study' => $row->study,
                    'status' => $row->status,
                    'faculty' => $faculty,
                    'faculty_role' => $faculty && method_exists($faculty, 'getRoleNames')
                        ? $faculty->getRoleNames()->first()
                        : null,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];
            });

        if ($inProgressStatus->isEmpty()) {
            return response()->json([], 404);
        }

        return response()->json(['data' => $inProgressStatus]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllApprovedStatus(Request $request)
    {
        $facultyId = $request->user()->id;
        $approvedStatus = StudyStatus::with(['student', 'faculty', 'study'])->where('status', 'Approved')->where('faculty_id', $facultyId)->get();
        if (!$approvedStatus) return response()->json([], 404);
        return response()->json(['data' => $approvedStatus]);
    }
}
