<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        $todos = Todo::orderByDesc('id')->paginate(15);
        return response()->json($todos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'completed' => ['sometimes', 'boolean'],
        ]);

        $todo = Todo::create([
            'title' => $validated['title'],
            'completed' => $validated['completed'] ?? false,
        ]);

        return response()->json($todo, Response::HTTP_CREATED);
    }

    public function update(Request $request, Todo $todo)
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'completed' => ['sometimes', 'boolean'],
        ]);

        $todo->update($validated);

        return response()->json($todo);
    }

    public function destroy(Todo $todo)
    {
        $todo->delete();
        return response()->noContent();
    }
}