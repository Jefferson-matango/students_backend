<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task\Subject;
use App\Models\Task\Task;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{

    public function index(Request $request, Subject $subject)
    {
        $name = $request->search;

        $tasks = $subject->tasks()->where('name', 'like', "%$name%")->orderBy('id', 'desc')->get();

        if ($tasks->isEmpty()) {
            return response()->json([
                'message' => 'The specified task could not be found or does not exist',
            ], 404);
        }    

        return response()->json([
            'data' => [
                'subject_name' => $subject->name,
                'tasks' => $tasks->map(function($task) {
                    return [
                        'id' => $task->id,
                        'name' => $task->name,
                        'description' => $task->description,
                        'state' => $task->state,
                        'created_at' => $task->created_at->format('Y-m-d h:i:s'),
                    ];
                }),
            ]
        ]);
    }

    public function indexAll(Request $request)
    {
        $name = $request->query('search', '');

        $subjects = Subject::with(['tasks' => function ($query) use ($name) {
            $query->where('name', 'like', "%$name%")->orderBy('id', 'desc');
        }])->get();

        if ($subjects->isEmpty()) {
            return response()->json([
                'message' => 'No tasks found for any subject',
            ], 404);
        }

        $data = $subjects->map(function ($subject) {
            return [
                'subject_name' => $subject->name,
                'tasks' => $subject->tasks->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'name' => $task->name,
                        'description' => $task->description,
                        'state' => $task->state,
                        'created_at' => $task->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
            ];
        });

        return response()->json([
            'data' => $data
        ]);
    }

    public function store(Request $request, Subject $subject)
    {

        try {
            $request->validate([
                'name' => 'required|string|unique:tasks,name|min:3',
                'description' => 'nullable',
                'state' => 'nullable|boolean',
            ], [
                'name.required' => 'The name is required',
                'name.string' => 'The name must be a string of characters',
                'name.unique' => 'The task name already exists',
                'name.min' => 'The name must be at least 3 characters',
                'state.boolean' => 'The state must be true or false',
            ]);

            $task = new Task($request->all());
            $task->subject_id = $subject->id;
            $task->save();

            return response()->json([
                'message' => 'Successfully created',
                'status' => '200',
                'task' => $task
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'status'=> 400,
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function show(Subject $subject, $id)
    {
        $task = $subject->tasks()->find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found or does not exist',
                'code' => 404,
            ], 404);
        }
    
        return response()->json([
            'message' => 'Task found',
            'code' => 200,
            'data' => [
                'subject_name' => $subject->name,
                'id' => $task->id,
                'name' => $task->name,
                'description' => $task->description,
                'state' => $task->state,
                'created_at' => $task->created_at->format('Y-m-d h:i:s'),
            ]
        ]);
    }

   
    public function update(Request $request, Subject $subject, $id)
    {
        try{
            $request->validate([
                'name' => 'required|string|min:3|unique:tasks,name,' . $id,
                'description' => 'nullable',
                'state' => 'nullable|boolean',
            ], [
                'name.required' => 'The name is required',
                'name.string' => 'The name must be a string of characters',
                'name.unique' => 'The task name already exists',
                'name.min' => 'The name must be at least 3 characters',
                'state.boolean' => 'The state must be true or false',
            ]);
        
            $task = $subject->tasks()->findOrFail($id);
            $task->update($request->all());
        
            return response()->json([
                'message' => 200,
                'message_text' => 'Successfully updated',
                'task' => $task
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'status'=> 400,
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   
    public function destroy(Subject $subject, $id)
    {
        $task = $subject->tasks()->find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found or does not exist',
                'code' => 404,
            ], 404);
        }

        $task->delete();

        return response()->json([
            'message' => 200,
            'message_text' => 'Successfully deleted',
        ]);
    }

    public function getSubjects(Request $request)
    {
        $subjects = Subject::all(['id', 'name']);

        if ($subjects->isEmpty()) {
            return response()->json([
                'message' => 'No subjects found',
            ], 404);
        }

        return response()->json([
            'message' => 'Subjects found',
            'code' => '200',
            'data' => $subjects
        ],200);
    }
}
