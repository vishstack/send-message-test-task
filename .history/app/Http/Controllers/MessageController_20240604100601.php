<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_user_id' => 'required|exists:users,id',
            'to_user_id' => 'required|exists:users,id',
            'type' => 'required|in:sms,email',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponse(null, $validator->errors(), 422), 422);
        }

        try {
            Message::create($validator->validated());
            return response()->json(apiResponse(null, 'Message sent successfully', 201), 201);
        } catch (\Exception $e) {
            return response()->json(apiResponse(null, 'Failed to send message', 500), 500);
        }
    }

    public function getConversationLogs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user1' => 'nullable',
            'user2' => 'nullable',
            'per_page' => 'nullable|integer|min:1',
            'page' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(apiResponse(null, $validator->errors(), 422), 422);
        }

        $validatedData = $validator->validated();

        try {
            $logsQuery = Message::query();

            if (isset($validatedData['user1'])) {
                $user1Param = $validatedData['user1'];
                if (filter_var($user1Param, FILTER_VALIDATE_EMAIL)) {
                    $user1 = User::where('email', $user1Param)->first();
                } else {
                    $user1 = User::find($user1Param);
                }

                if (!$user1) {
                    return response()->json(apiResponse(null, 'User not found for user1', 404), 404);
                }

                if (isset($validatedData['user2'])) {
                    $user2Param = $validatedData['user2'];
                    if (filter_var($user2Param, FILTER_VALIDATE_EMAIL)) {
                        $user2 = User::where('email', $user2Param)->first();
                    } else {
                        $user2 = User::find($user2Param);
                    }

                    if (!$user2) {
                        return response()->json(apiResponse(null, 'User not found for user2', 404), 404);
                    }

                    // If both user1 and user2 are provided, fetch the conversation between the two users
                    $logsQuery->where(function($query) use ($user1, $user2) {
                        $query->where(function($q) use ($user1, $user2) {
                            $q->where('from_user_id', $user1->id)
                            ->where('to_user_id', $user2->id);
                        })->orWhere(function($q) use ($user1, $user2) {
                            $q->where('from_user_id', $user2->id)
                            ->where('to_user_id', $user1->id);
                        });
                    });
                } else {
                    // If only user1 is provided, fetch all conversations involving this user
                    $logsQuery->where(function($query) use ($user1) {
                        $query->where('from_user_id', $user1->id)
                            ->orWhere('to_user_id', $user1->id);
                    });
                }
            } else {
                return response()->json(apiResponse(null, 'At least user1 parameter is required', 422), 422);
            }

            // Get per_page and page parameters or set default values
            $perPage = $validatedData['per_page'] ?? 5;
            $currentPage = $validatedData['page'] ?? 1;

            $logsPaginated = $logsQuery->paginate($perPage, ['*'], 'page', $currentPage);

            $logs = $logsPaginated->getCollection()->map(function ($log) {
                return [
                    'id' => $log->id,
                    'from_user' => $log->fromUser->name,
                    'to_user' => $log->toUser->name,
                    'type' => $log->type,
                    'message' => $log->message,
                    'created_at' => $log->created_at->toDateTimeString(),
                ];
            });

            $paginationData = [
                'current_page' => $logsPaginated->currentPage(),
                'previous_page' => $logsPaginated->previousPageUrl(),
                'next_page' => $logsPaginated->nextPageUrl(),
                'total_pages' => $logsPaginated->lastPage(),
                'total_items' => $logsPaginated->total(),
                'items_per_page' => $logsPaginated->perPage(),
            ];

            $response = [
                'logs' => $logs,
                'pagination' => $paginationData,
            ];

            return response()->json(apiResponse($response, 'Data fetched successfully', 200), 200);
        } catch (\Exception $e) {
            return response()->json(apiResponse(null, 'Failed to retrieve conversation logs', 500), 500);
        }
    }
}
