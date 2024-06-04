<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

class MessageController extends Controller
{
    /**
     * Send a message from one user to another.
     *
     * @param Request $request The incoming HTTP request containing the following parameters:
     *                         - from_user_id: The ID of the user sending the message (required).
     *                         - to_user_id: The ID of the user receiving the message (required).
     *                         - type: The type of message being sent (required, must be either 'sms' or 'email').
     *                         - message: The content of the message (required, must be a string).
     *
     * @return \Illuminate\Http\JsonResponse JSON response containing:
     *                                       - A success message if the message is sent successfully.
     *                                       - Validation error messages if the input validation fails.
     *                                       - Appropriate HTTP status codes and messages.
     *
     * @throws \Exception If there is an error during the message creation process.
     */
    public function sendMessage(Request $request)
    {
        // Validate the incoming request parameters.
        $validator = Validator::make($request->all(), [
            'from_user_id' => 'required|exists:users,id',
            'to_user_id' => 'required|exists:users,id',
            'type' => 'required|in:sms,email',
            'message' => 'required|string',
        ]);

        // If validation fails, return a 422 Unprocessable Entity response with error messages.
        if ($validator->fails()) {
            return apiResponse(null, $validator->errors(), 422);
        }

        try {
            // Create a new message record in the database using the validated data.
            Message::create($validator->validated());

            // Return a 201 Created response indicating the message was sent successfully.
            return apiResponse(null, 'Message sent successfully', 201);
        } catch (\Exception $e) {
            // If an exception occurs, return a 500 Internal Server Error response.
            return apiResponse(null, 'Failed to send message', 500);
        }
    }

    /**
     * Retrieve conversation logs between two users with pagination.
     *
     * @param Request $request The incoming HTTP request, containing optional parameters:
     *                         - user1: The first user ID or email (required).
     *                         - user2: The second user ID or email (optional).
     *                         - per_page: Number of items per page (optional, default is 5).
     *                         - page: The page number to retrieve (optional, default is 1).
     *
     * @return \Illuminate\Http\JsonResponse JSON response containing:
     *                                       - The paginated conversation logs.
     *                                       - Pagination metadata (current page, previous page, next page, total pages, total items, items per page).
     *                                       - Appropriate HTTP status codes and messages.
     *
     * @throws \Exception If there is an error during the retrieval process.
     */
    public function getConversationLogs(Request $request)
    {
        // Validate incoming request parameters.
        $validator = Validator::make($request->all(), [
            'user1' => 'nullable',
            'user2' => 'nullable',
            'per_page' => 'nullable|integer|min:1',
            'page' => 'nullable|integer|min:1',
        ]);

        // If validation fails, return a 422 Unprocessable Entity response with error messages.
        if ($validator->fails()) {
            return apiResponse(null, $validator->errors(), 422);
        }

        // Retrieve validated data from the request.
        $validatedData = $validator->validated();

        try {
            // Initialize a query for Message model.
            $logsQuery = Message::query();

            // Check if user1 parameter is provided and retrieve the corresponding User model.
            if (isset($validatedData['user1'])) {
                $user1Param = $validatedData['user1'];
                if (filter_var($user1Param, FILTER_VALIDATE_EMAIL)) {
                    $user1 = User::where('email', $user1Param)->first();
                } else {
                    $user1 = User::find($user1Param);
                }

                // If user1 is not found, return a 404 Not Found response.
                if (!$user1) {
                    return apiResponse(null, 'User not found for user1', 404);
                }

                // If user2 parameter is provided, retrieve the corresponding User model.
                if (isset($validatedData['user2'])) {
                    $user2Param = $validatedData['user2'];
                    if (filter_var($user2Param, FILTER_VALIDATE_EMAIL)) {
                        $user2 = User::where('email', $user2Param)->first();
                    } else {
                        $user2 = User::find($user2Param);
                    }

                    // If user2 is not found, return a 404 Not Found response.
                    if (!$user2) {
                        return apiResponse(null, 'User not found for user2', 404);
                    }

                    // Fetch the conversation logs between user1 and user2.
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
                    // If only user1 is provided, fetch all conversations involving user1.
                    $logsQuery->where(function($query) use ($user1) {
                        $query->where('from_user_id', $user1->id)
                            ->orWhere('to_user_id', $user1->id);
                    });
                }
            } else {
                // If user1 parameter is missing, return a 422 Unprocessable Entity response.
                return apiResponse(null, 'At least user1 parameter is required', 422);
            }

            // Get per_page and page parameters from validated data or set default values.
            $perPage = $validatedData['per_page'] ?? 5;
            $currentPage = $validatedData['page'] ?? 1;

            // Apply pagination to the query.
            $logsPaginated = $logsQuery->paginate($perPage, ['*'], 'page', $currentPage);

            // Transform the paginated logs collection.
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

            // Extract pagination metadata.
            $paginationData = [
                'current_page' => $logsPaginated->currentPage(),
                'previous_page' => ($logsPaginated->currentPage() > 1) ? $logsPaginated->currentPage() - 1 : null,
                'next_page' => ($logsPaginated->currentPage() < $logsPaginated->lastPage()) ? $logsPaginated->currentPage() + 1 : null,
                'total_pages' => $logsPaginated->lastPage(),
                'total_items' => $logsPaginated->total(),
                'items_per_page' => $logsPaginated->perPage(),
            ];

            // Combine the transformed logs and pagination metadata into the response.
            $response = [
                'data' => $logs,
                'pagination' => $paginationData,
            ];

            // Return a 200 OK response with the combined data.
            return apiResponse($response, 'Data fetched successfully', 200);
        } catch (\Exception $e) {
            // If an exception occurs, return a 500 Internal Server Error response.
            return apiResponse(null, 'Failed to retrieve conversation logs', 500);
        }
    }
}
