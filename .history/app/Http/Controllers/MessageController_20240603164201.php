<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

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
        $validatedData = $request->validate([
            'id' => 'nullable|exists:users,id',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $logsQuery = Message::query();

        if (isset($validatedData['id'])) {
            $logsQuery->where('from_user_id', $validatedData['id'])
                      ->orWhere('to_user_id', $validatedData['id']);
        }

        if (isset($validatedData['phone']) || isset($validatedData['email'])) {
            $logsQuery->whereHas('fromUser', function ($query) use ($validatedData) {
                $query->where('phone', $validatedData['phone'])
                      ->orWhere('email', $validatedData['email']);
            })->orWhereHas('toUser', function ($query) use ($validatedData) {
                $query->where('phone', $validatedData['phone'])
                      ->orWhere('email', $validatedData['email']);
            });
        }

        // $logs = $logsQuery->get();

        $logs = $logsQuery->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'from_user' => $log->fromUser->name,
                'to_user' => $log->toUser->name,
                'type' => $log->type,
                'message' => $log->message,
                'created_at' => $log->created_at,
            ];
        });

        return response()->json(['logs' => $logs]);
    }
}
