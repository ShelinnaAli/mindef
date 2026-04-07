<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Middleware\ValidateCamelCaseKeys;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ReflectionClass;

class ValidateCamelCaseKeysTest extends TestCase
{
    protected $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new ValidateCamelCaseKeys();
    }

    /**
     * Test that valid camelCase keys are accepted
     */
    public function test_valid_camelcase_keys_are_accepted(): void
    {
        $request = Request::create('/test', 'POST', [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'emailAddress' => 'john@example.com',
            'isActive' => true,
            'userId' => 123
        ]);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('success', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $response->getContent());
    }

    /**
     * Test that snake_case keys are rejected
     */
    public function test_snake_case_keys_are_rejected(): void
    {
        $request = Request::create('/test', 'POST', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email_address' => 'john@example.com'
        ]);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('success', 200);
        });

        $this->assertEquals(422, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Request data keys must be in camelCase format', $responseData['message']);
        $this->assertArrayHasKey('invalid_keys', $responseData['errors']);

        $invalidKeys = $responseData['errors']['invalid_keys'];
        $this->assertContains('first_name', $invalidKeys);
        $this->assertContains('last_name', $invalidKeys);
        $this->assertContains('email_address', $invalidKeys);
    }

    /**
     * Test that nested invalid keys are detected
     */
    public function test_nested_invalid_keys_are_detected(): void
    {
        $request = Request::create('/test', 'POST', [
            'validKey' => 'value',
            'userSettings' => [
                'validNested' => true,
                'invalid_nested' => false,
                'deepSettings' => [
                    'validDeep' => true,
                    'invalid_deep' => false
                ]
            ]
        ]);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('success', 200);
        });

        $this->assertEquals(422, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $invalidKeys = $responseData['errors']['invalid_keys'];

        $this->assertContains('userSettings.invalid_nested', $invalidKeys);
        $this->assertContains('userSettings.deepSettings.invalid_deep', $invalidKeys);
    }

    /**
     * Test that GET requests are not validated
     */
    public function test_get_requests_are_not_validated(): void
    {
        $request = Request::create('/test', 'GET', [
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('success', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $response->getContent());
    }

    /**
     * Test that only POST, PUT, and PATCH methods are validated
     */
    public function test_only_specific_methods_are_validated(): void
    {
        $invalidData = ['first_name' => 'John'];

        // Test methods that should not be validated
        $nonValidatedMethods = ['GET', 'HEAD', 'DELETE', 'OPTIONS'];

        foreach ($nonValidatedMethods as $method) {
            $request = Request::create('/test', $method, $invalidData);

            $response = $this->middleware->handle($request, function ($req) {
                return new Response('success', 200);
            });

            $this->assertEquals(200, $response->getStatusCode(), "Method {$method} should not be validated");
        }

        // Test methods that should be validated
        $validatedMethods = ['POST', 'PUT', 'PATCH'];

        foreach ($validatedMethods as $method) {
            $request = Request::create('/test', $method, $invalidData);

            $response = $this->middleware->handle($request, function ($req) {
                return new Response('success', 200);
            });

            $this->assertEquals(422, $response->getStatusCode(), "Method {$method} should be validated");
        }
    }

    /**
     * Test the isCamelCase method with various inputs
     */
    public function test_is_camel_case_method(): void
    {
        $reflection = new ReflectionClass($this->middleware);
        $method = $reflection->getMethod('isCamelCase');
        $method->setAccessible(true);

        $testCases = [
            // Valid camelCase
            'validCamelCase' => true,
            'firstName' => true,
            'isActive' => true,
            'lowercase' => true,
            'a' => true,
            'test123' => true,
            'userId' => true,

            // Invalid cases
            'first_name' => false,        // snake_case
            'First_Name' => false,        // Pascal_Case with underscore
            'FirstName' => false,         // PascalCase (starts with uppercase)
            'XMLHttpRequest' => false,    // consecutive uppercase letters
            'first-name' => false,        // kebab-case
            'UPPER_CASE' => false,        // SCREAMING_SNAKE_CASE
            'first name' => false,        // spaces
            'first.name' => false,        // dots

            // Edge cases
            '123' => true,                // numeric string
            '0' => true,                  // zero
            '' => true,                   // empty string (allowed)
        ];

        foreach ($testCases as $key => $expected) {
            $result = $method->invoke($this->middleware, $key);
            $this->assertEquals(
                $expected,
                $result,
                "Key '{$key}' should be " . ($expected ? 'valid' : 'invalid')
            );
        }
    }

    /**
     * Test validateCamelCaseKeys method directly
     */
    public function test_validate_camel_case_keys_method(): void
    {
        $reflection = new ReflectionClass($this->middleware);
        $method = $reflection->getMethod('validateCamelCaseKeys');
        $method->setAccessible(true);

        // Test with mixed valid and invalid keys
        $data = [
            'validKey' => 'value',
            'invalid_key' => 'value',
            'anotherValid' => 'value',
            'another_invalid' => 'value'
        ];

        $invalidKeys = $method->invoke($this->middleware, $data);

        $this->assertCount(2, $invalidKeys);
        $this->assertContains('invalid_key', $invalidKeys);
        $this->assertContains('another_invalid', $invalidKeys);
        $this->assertNotContains('validKey', $invalidKeys);
        $this->assertNotContains('anotherValid', $invalidKeys);
    }

    /**
     * Test that numeric keys are allowed
     */
    public function test_numeric_keys_are_allowed(): void
    {
        $request = Request::create('/test', 'POST', [
            'validKey' => 'value',
            '0' => 'first item',
            '1' => 'second item',
            '123' => 'numbered item'
        ]);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('success', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test complex nested structure with arrays
     */
    public function test_complex_nested_structure(): void
    {
        $request = Request::create('/test', 'POST', [
            'userData' => [
                'personalInfo' => [
                    'firstName' => 'John',
                    'last_name' => 'Doe', // Invalid
                    'contacts' => [
                        'validEmail' => 'john@example.com',
                        'phone_number' => '123456789' // Invalid
                    ]
                ],
                'preferences' => [
                    'validSetting' => true,
                    'invalid_setting' => false // Invalid
                ]
            ]
        ]);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('success', 200);
        });

        $this->assertEquals(422, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $invalidKeys = $responseData['errors']['invalid_keys'];

        $expectedInvalidKeys = [
            'userData.personalInfo.last_name',
            'userData.personalInfo.contacts.phone_number',
            'userData.preferences.invalid_setting'
        ];

        foreach ($expectedInvalidKeys as $expectedKey) {
            $this->assertContains($expectedKey, $invalidKeys);
        }
    }

    /**
     * Test that empty arrays don't cause issues
     */
    public function test_empty_arrays_are_handled(): void
    {
        $request = Request::create('/test', 'POST', [
            'validKey' => 'value',
            'emptyArray' => [],
            'nestedEmpty' => [
                'anotherEmpty' => []
            ]
        ]);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('success', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test the actual API endpoint that uses this middleware
     */
    public function test_api_endpoint_with_camelcase_validation(): void
    {
        // Test with valid camelCase data
        $validResponse = $this->postJson('/api/test-camelcase', [
            'testField' => 'value',
            'anotherField' => 'another value'
        ]);

        $validResponse->assertStatus(200);

        // Test with invalid snake_case data
        $invalidResponse = $this->postJson('/api/test-camelcase', [
            'test_field' => 'value',
            'another_field' => 'another value'
        ]);

        $invalidResponse->assertStatus(422)
                       ->assertJson([
                           'success' => false,
                           'message' => 'Request data keys must be in camelCase format'
                       ]);
    }
}
