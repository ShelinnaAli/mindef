<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ValidateCamelCaseKeys
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next): ResponseAlias
    {
        // Only validate POST, PUT, and PATCH methods
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return $next($request);
        }

        // Get all request data
        $data = $request->all();

        // Validate keys are in camelCase
        $invalidKeys = $this->validateCamelCaseKeys($data);

        if (! empty($invalidKeys)) {
            return response()->json([
                'success' => false,
                'message' => 'Request data keys must be in camelCase format',
                'errors' => [
                    'invalid_keys' => $invalidKeys,
                    'details' => 'The following keys are not in camelCase format: '.implode(', ', $invalidKeys),
                ],
            ], 422);
        }

        return $next($request);
    }

    /**
     * Recursively validate that all keys in the data are in camelCase format
     */
    private function validateCamelCaseKeys(array $data, string $prefix = ''): array
    {
        $invalidKeys = [];

        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            // Check if the key is in camelCase format
            if (! $this->isCamelCase($key)) {
                $invalidKeys[] = $fullKey;
            }

            // If the value is an array, recursively validate its keys
            if (is_array($value)) {
                $nestedInvalidKeys = $this->validateCamelCaseKeys($value, $fullKey);
                $invalidKeys = array_merge($invalidKeys, $nestedInvalidKeys);
            }
        }

        return $invalidKeys;
    }

    /**
     * Check if a string is in camelCase format
     */
    private function isCamelCase(string $key): bool
    {
        // Allow empty strings or numeric keys
        if (empty($key) || is_numeric($key)) {
            return true;
        }

        // camelCase should:
        // 1. Start with a lowercase letter
        // 2. Only contain letters and numbers
        // 3. Not contain underscores, hyphens, or spaces
        // 4. Not have consecutive uppercase letters (like XMLHttpRequest)

        // Basic pattern: starts with lowercase, followed by letters/numbers
        if (! preg_match('/^[a-z][a-zA-Z0-9]*$/', $key)) {
            return false;
        }

        // Additional check: should not have consecutive uppercase letters
        if (preg_match('/[A-Z]{2,}/', $key)) {
            return false;
        }

        return true;
    }
}
