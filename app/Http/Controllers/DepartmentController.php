<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;

class DepartmentController extends Controller
{
    // listar todos los departamentos
    public function index()
    {
        return response()->json(Department::all());
    }

    // obtener un departamento
    public function show($id)
    {
        $dept = Department::find($id);
        if (!$dept) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json($dept);
    }

    // crear departamento
    public function store(Request $request)
    {
        $data = $request->validate([
            'number' => 'required|string|unique:departments,number',
            'block' => 'nullable|string',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
            'area' => 'nullable|integer',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $dept = Department::create($data);
        return response()->json($dept, 201);
    }

    // actualizar departamento
    public function update(Request $request, $id)
    {
        $dept = Department::find($id);
        if (!$dept) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $data = $request->validate([
            'number' => 'nullable|string|unique:departments,number,' . $id,
            'block' => 'nullable|string',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
            'area' => 'nullable|integer',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $dept->update($data);
        return response()->json($dept);
    }

    // eliminar departamento
    public function destroy($id)
    {
        $dept = Department::find($id);
        if (!$dept) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $dept->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
