<?php

namespace App\Http\Controllers;

use App\Services\EncryptionService;
use Illuminate\Support\Str;

abstract class Controller
{
    // create response method with encryption
    protected function response(
        string $message = 'Success',
        array $data = [],
        bool $withEncryption = false,
        int $status = 200
    ) {
        $data = $this->convertKeysToCamelCase($data);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data && $withEncryption ? $this->encryptData($data) : $data,
        ], $status);
    }

    // create error response method
    protected function errorResponse(string $message, int $status = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    // create validation error response method
    protected function validationErrorResponse(array $errors, int $status = 422)
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors,
        ], $status);
    }

    // decrypt data
    protected function decryptData(string $data, array $mergeFields = [])
    {
        // If $mergeFields is provided, merge it with the decrypted data
        $decryptedData = EncryptionService::decrypt($data);
        if (! empty($mergeFields)) {
            $decryptedData = array_merge($decryptedData, $mergeFields);
        }

        return $decryptedData;
    }

    // encrypt data
    protected function encryptData(array $data)
    {
        return EncryptionService::encrypt($data);
    }

    // recursively convert array keys to camelCase
    private function convertKeysToCamelCase($data)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $camelKey = is_string($key) ? Str::camel($key) : $key;
                $result[$camelKey] = $this->convertKeysToCamelCase($value);
            }

            return $result;
        }

        return $data;
    }

    /**
     * Generate field mapping from model's fillable fields
     * Converts snake_case fillable fields to camelCase keys dynamically
     *
     * @param  string  $modelClass  The model class name (e.g., 'App\Models\Programme')
     * @return array Array with camelCase keys and snake_case values
     */
    protected function getFieldMapping(string $modelClass): array
    {
        $fillableFields = (new $modelClass)->getFillable();
        $fieldMapping = [];

        foreach ($fillableFields as $snakeField) {
            // Convert snake_case to camelCase
            $camelField = lcfirst(str_replace('_', '', ucwords($snakeField, '_')));
            $fieldMapping[$camelField] = $snakeField;
        }

        return $fieldMapping;
    }

    /**
     * Convert camelCase input data to snake_case based on model's fillable fields
     * Only processes fields that are both in the input data and in the model's fillable array
     *
     * @param  array  $data  Input data in camelCase format
     * @param  string  $modelClass  The model class name (e.g., 'App\Models\Programme')
     * @param  array  $excludeFields  Fields to exclude from mapping (e.g., ['created_by'])
     * @return array Mapped data in snake_case format
     */
    protected function mapInputData(array $data, string $modelClass, array $excludeFields = []): array
    {
        $fieldMapping = $this->getFieldMapping($modelClass);

        // Exclude specified fields if needed
        if (! empty($excludeFields)) {
            $fieldMapping = array_filter($fieldMapping, fn ($snakeKey) => ! in_array($snakeKey, $excludeFields));
        }

        $mappedData = [];

        foreach ($fieldMapping as $camelKey => $snakeKey) {
            if (array_key_exists($camelKey, $data)) {
                $mappedData[$snakeKey] = $data[$camelKey];
            }
        }

        return $mappedData;
    }

    /**
     * Handle expand parameter to load model relationships dynamically
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model  The model instance
     * @param  \Illuminate\Http\Request  $request  The "expand" request instance
     * @return \Illuminate\Database\Eloquent\Model The model with loaded relationships
     */
    protected function loadRelationModel($model, $expandRequest)
    {
        if ($expandRequest) {
            $expand = explode(',', $expandRequest);

            foreach ($expand as $relation) {
                $relation = trim($relation);
                if (method_exists($model->getModel(), $relation)) {
                    $model->load($relation);
                }
            }
        }

        return $model;
    }
}
