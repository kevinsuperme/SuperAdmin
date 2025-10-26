<?php

namespace think\testing;

/**
 * Stub file for ThinkPHP TestCase class
 * This file helps IDE recognize the TestCase class and its methods
 */
abstract class TestCase
{
    /**
     * Send a GET request to the application.
     *
     * @param string $uri
     * @param array $headers
     * @return \Tests\Feature\TestResponse
     */
    protected function get($uri, array $headers = [])
    {
        // Implementation is not needed for IDE stub
    }
    
    /**
     * Send a POST request to the application.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return \Tests\Feature\TestResponse
     */
    protected function post($uri, array $data = [], array $headers = [])
    {
        // Implementation is not needed for IDE stub
    }
    
    /**
     * Assert that a value is not null.
     *
     * @param mixed $value
     * @param string $message
     */
    protected function assertNotNull($value, string $message = '')
    {
        // Implementation is not needed for IDE stub
    }
    
    /**
     * Assert that two values are equal.
     *
     * @param mixed $expected
     * @param mixed $actual
     * @param string $message
     */
    protected function assertEquals($expected, $actual, string $message = '')
    {
        // Implementation is not needed for IDE stub
    }
    
    /**
     * Create a test user.
     *
     * @param array $data
     * @return int
     */
    protected function createTestUser(array $data = []): int
    {
        // Implementation is not needed for IDE stub
    }
    
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        // Implementation is not needed for IDE stub
    }
    
    /**
     * Teardown the test environment.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // Implementation is not needed for IDE stub
    }
}