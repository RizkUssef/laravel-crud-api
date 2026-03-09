<?php

namespace Rizkussef\LaravelCrudApi\Traits;

trait ApiResponse
{
    public function success($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'code' => $code
        ], $code);
    }

    public function error($data, $message = 'Error', $code = 500)
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'code' => $code
        ], $code);
    }
}