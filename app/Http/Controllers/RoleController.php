<?php

namespace App\Http\Controllers;

use App\Models\Role;

class RoleController extends Controller
{
    /**
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $roles = Role::whereNotIn('name', ['Student','Faculty'])->get()->map(function ($row) {
            return [
                'role' => $row->name,
            ];
        });

        if (!$roles) return response()->json(['message' => 'No roles available please try again later.'], 404);

        return response()->json([
            'data' => $roles,
        ], 200);
    }
}
