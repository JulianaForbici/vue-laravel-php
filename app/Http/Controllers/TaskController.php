<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = auth()->user()->tasks()->latest()->get();

        return Inertia::render('Dashboard', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
     public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'title' => 'required|string|min:3|max:120',
                'description' => 'nullable|string',
                'status' => 'required|in:todo,doing,done',
                'due_date' => 'nullable|date|after_or_equal:today',
            ],
            [
                'title.required' => 'O título é obrigatório.',
                'due_date.after_or_equal' => 'A data de vencimento não pode ser no passado.',
            ]
        );

        auth()->user()->tasks()->create($validated);

        return response()->json(['message' => 'Tarefa criada com sucesso!']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        return Inertia::render('tasks/Edit', [
            'task' => $task,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

            $validated = $request->validate(
            [
                'title' => 'required|string|min:3|max:120',
                'description' => 'nullable|string',
                'status' => 'required|in:todo,doing,done',
                'due_date' => 'nullable|date|after_or_equal:today',
            ],
            [
                'title.required' => 'O título é obrigatório.',
                'due_date.after_or_equal' => 'A data de vencimento não pode ser no passado.',
            ]
        );

        $task->update($validated);

        return response()->json(['message' => 'Tarefa atualizada com sucesso!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Tarefa excluída com sucesso!']);
    }
}
