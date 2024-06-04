<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $message = Message::create($validator->validated());
            return response()->json(['message' => 'Message sent successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send message'], 500);
        }
    }

    public function getConversationLogs(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'nullable|exists:users,id',
        'phone' => 'nullable|string',
        'email' => 'nullable|email',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $validatedData = $validator->validated();

    try {
        $logsQuery = Message::query();

        if (isset($validatedData['id'])) {
            $logsQuery->where(function($query) use ($validatedData) {
                $query->where('from_user_id', $validatedData['id'])
                      ->orWhere('to_user_id', $validatedData['id']);
            });
        }

        if (isset($validatedData['phone']) || isset($validatedData['email'])) {
            $logsQuery->where(function ($query) use ($validatedData) {
                $query->whereHas('fromUser', function ($query) use ($validatedData) {
                    if (isset($validatedData['phone'])) {
                        $query->where('phone', $validatedData['phone']);
                    }
                    if (isset($validatedData['email'])) {
                        $query->orWhere('email', $validatedData['email']);
                    }
                })->orWhereHas('toUser', function ($query) use ($validatedData) {
                    if (isset($validatedData['phone'])) {
                        $query->where('phone', $validatedData['phone']);
                    }
                    if (isset($validatedData['email'])) {
                        $query->orWhere('email', $validatedData['email']);
                    }
                });
            });
        }

        $logs = $logsQuery->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'from_user' => $log->fromUser->name,
                'to_user' => $log->toUser->name,
                'type' => $log->type,
                'message' => $log->message,
                'created_at' => $log->created_at->toDateTimeString(),
            ];
        });

        return response()->json(['logs' => $logs]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to retrieve conversation logs'], 500);
    }
}
}
