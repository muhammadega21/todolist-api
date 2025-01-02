<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TodoRequest;
use App\Models\Todo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{
    public function index()
    {
        $todos = Todo::orderBy('status')->latest('created_at')->get();

        if ($todos->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "data empty",
            ], Response::HTTP_OK);
        }

        try {
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "list todo",
                'data' => $todos->map(function ($todo) {
                    return [
                        'name' => $todo->name,
                        'status' => $todo->status,
                        'created_at' => $todo->created_at->format('Y-m-d H:i:s')
                    ];
                })
            ]);
        } catch (Exception $e) {
            Log::error("Error get data: " .  $e->getMessage());

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => "Error get data"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:20|unique:todos,name'
        ], [
            'name.required' => 'Name cannot be empty!',
            'name.min' => 'Name at least 3 characters!',
            'name.max' => 'Name up to 20 characters!',
            'name.unique' => 'Name already exists'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            Todo::create([
                'name' => $request->input('name')
            ]);
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Success add todo'
            ], Response::HTTP_OK);
        } catch (Exception $e) {

            Log::error("Error add todo: " .  $e->getMessage());

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => "Error add todo"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
        $todo = Todo::find($id);

        if (!$todo) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Todo not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $rules = [];
        if ($request->input('name') != $todo->name) {
            $rules['name'] = 'required|min:3|max:20|unique:todos,name';
        }

        $validator = Validator::make($request->all(), $rules, [
            'name.required' => 'Name cannot be empty!',
            'name.min' => 'Name at least 3 characters!',
            'name.max' => 'Name up to 20 characters!',
            'name.unique' => 'Name already exists'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $todo->update([
                'name' => $request->input('name'),
                'status' => $request->input('status')
            ]);
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Success update todo'
            ], Response::HTTP_OK);
        } catch (Exception $e) {

            Log::error("Error update todo: " .  $e->getMessage());

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => "Error update todo"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        $todo = Todo::find($id);

        if (!$todo) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Todo not found'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $todo->delete();
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Success delete todo"
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error("Error delete todo: " .  $e->getMessage());

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => "Error delete todo"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
