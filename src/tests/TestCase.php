<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Create a user for testing
     *
     * @param array $attributes
     * @return \App\Models\User
     */
    protected function createUser(array $attributes = []): \App\Models\User
    {
        return \App\Models\User::factory()->create($attributes);
    }

    /**
     * Create an admin user for testing
     *
     * @param array $attributes
     * @return \App\Models\User
     */
    protected function createAdmin(array $attributes = []): \App\Models\User
    {
        return \App\Models\User::factory()->create(array_merge(['role' => 'admin'], $attributes));
    }
}
