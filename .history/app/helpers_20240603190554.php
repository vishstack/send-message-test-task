// app/helpers.php

if (!function_exists('apiResponse')) {
    /**
     * Generate API response.
     *
     * @param mixed|null $data
     * @param string|null $message
     * @param int $statusCode
     * @return array
     */
    function apiResponse($data = null, $message = null, $statusCode = 200)
    {
        $response = [
            'status' => $statusCode,
            'data' => $data ?? [],
            'message' => $message,
        ];

        // If data is provided, and there is no error message, consider it a success
        if (!is_null($data) && is_null($message)) {
            $response['status'] = $statusCode;
        } else {
            $response['status'] = $statusCode >= 400 ? $statusCode : 500;
        }

        return $response;
    }
}
