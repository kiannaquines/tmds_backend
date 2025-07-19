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
        $excludedRoles = [
            'Student',
            'Department Research Coordinator',
            'Department Chairperson',
            'College Research Coordinator',
            'College Dean',
        ];

        $users = User::whereDoesntHave('roles', function ($query) use ($excludedRoles) {
            $query->whereIn('name', $excludedRoles);
        })->select('id', 'name')->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No faculty as of the moment.'], 404);
        }

        return response()->json($users);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function department(Request $request)
    {
        $departments = [
            ['id' => 1, 'name' => 'Department of Accountancy'],
            ['id' => 2, 'name' => 'Department of Agribusiness'],
            ['id' => 3, 'name' => 'Department of Agricultural Economics'],
            ['id' => 4, 'name' => 'Department of Business Administration'],
            ['id' => 5, 'name' => 'Department of Development Management'],
        ];

        return response()->json($departments);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function study(Request $request)
    {
        $study = [
            ['id' => 1, 'type' => 'Manuscript'],
            ['id' => 2, 'type' =>  'Outline'],
        ];
        return response()->json($study);
    }
}
