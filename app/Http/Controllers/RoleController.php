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
        $roles = Role::where('name', '!=', 'Student')->get()->map(function ($row) {
            return [
                'role' => $row->name,
            ];
        });

        return response()->json([
            'data' => $roles,
        ], 200);
    }
}
