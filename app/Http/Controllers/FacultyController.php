<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class FacultyController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function faculty(Request $request)
    {
        $users = User::role('Faculty')->select('id', 'name')->get();

        if (!$users) return response()->json(['message' => 'No faculty as of the moment.'], 404);

        return response()->json($users);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function department(Request $request)
    {
        $departments = [
            'Department of Accountancy',
            'Department of Agribusiness',
            'Department of Agricultural Economics',
            'Department of Business Administration',
            'Department of Development Management',
        ];

        return response()->json($departments);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function study(Request $request)
    {
        $study = [
            'Manuscript',
            'Outline',
        ];

        return response()->json($study);
    }
}
