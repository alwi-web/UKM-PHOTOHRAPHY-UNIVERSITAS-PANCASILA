<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/** Login sebagai user aktif dengan role tertentu. */
function loginAs(Role $role = Role::Member, array $attributes = []): User
{
    $user = User::factory()->role($role)->create($attributes);
    test()->actingAs($user);

    return $user;
}
