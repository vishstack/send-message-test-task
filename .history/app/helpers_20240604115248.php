<?php

if (!function_exists('apiResponse')) {
    /**
     * Generate a standardized API response.
     *
     * @param mixed $data The data to be returned in the response (default is null, which will be converted to an empty array).
     * @param string|null $message A message to be included in the response (default is null).
     * @param int $statusCode The HTTP status code to be returned in the response (default is 200).
     *
     * @return array An array containing:
     *               - 'status': The HTTP status code.
     *               - 'data': The data payload of the response (defaults to an empty array if null).
     *               - 'message': A message describing the response.
     */
    function apiResponse($data = null, $message = null, $statusCode = 200)
    {
        // Create a response array with the provided status code, data, and message.
        $response = [
            'status' => $statusCode,
            'data' => $data ?? [],
            'message' => $message,
        ];

        // Return the response json.
        return response()->json($response, $statusCode);
    }
}
