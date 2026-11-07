<?php

namespace App\Http\Controllers;

use App\Support\LogContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

abstract class Controller
{
    /**
     * Same as $request->validate(), but writes a structured warning when the input is rejected.
     * Only the NAMES of the failing fields are logged, never the submitted values
     * (they can be private, e.g. a password). The exception is re-thrown untouched,
     * so the HTTP response the client receives does not change.
     */
    protected function validateOrLog(Request $request, array $rules, string $action): array
    {
        try {
            return $request->validate($rules);
        } catch (ValidationException $e) {
            Log::warning('validation.failed', [
                'who' => LogContext::who(),
                'what' => "validation of the '{$action}' request",
                'why' => 'submitted input did not satisfy the validation rules',
                'failed_fields' => array_keys($e->errors()),
            ]);

            throw $e;
        }
    }
}
