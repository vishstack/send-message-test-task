<?php

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

        return $response;
    }
}
