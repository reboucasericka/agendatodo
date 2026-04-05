<?php

use App\Models\TaskModel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

test('authenticated user sees only own tasks on app', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    TaskModel::create([
        'title' => 'Mine',
        'user_id' => $user->id,
        'priority' => 'medium',
        'status' => 'pending',
    ]);
    TaskModel::create([
        'title' => 'Other',
        'user_id' => $other->id,
        'priority' => 'medium',
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->get(route('app'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tasks/TaskApp')
            ->has('tasks', 1)
            ->has('meetings')
            ->where('tasks.0.title', 'Mine'));
});

test('user can create a task', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->post(route('tasks.store'), [
            'title' => 'Nova',
            'description' => null,
            'due_date' => null,
            'priority' => 'high',
        ])
        ->assertRedirect();

    expect(TaskModel::where('user_id', $user->id)->where('title', 'Nova')->exists())->toBeTrue();
});

test('user can update own task', function () {
    $user = User::factory()->create();
    $task = TaskModel::create([
        'title' => 'Old',
        'user_id' => $user->id,
        'priority' => 'low',
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->put(route('tasks.update', $task), [
            'title' => 'New title',
            'description' => 'Desc',
            'due_date' => '2026-05-01',
            'priority' => 'medium',
        ])
        ->assertRedirect();

    $task->refresh();
    expect($task->title)->toBe('New title');
    expect($task->priority)->toBe('medium');
});

test('user can toggle task completion', function () {
    $user = User::factory()->create();
    $task = TaskModel::create([
        'title' => 'T',
        'user_id' => $user->id,
        'priority' => 'medium',
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->post(route('tasks.toggle', $task))
        ->assertRedirect();

    expect($task->fresh()->status)->toBe(TaskModel::STATUS_COMPLETED);
});

test('user can delete own task', function () {
    $user = User::factory()->create();
    $task = TaskModel::create([
        'title' => 'T',
        'user_id' => $user->id,
        'priority' => 'medium',
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->delete(route('tasks.destroy', $task))
        ->assertRedirect();

    expect(TaskModel::find($task->id))->toBeNull();
});

test('user cannot modify another users task', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $task = TaskModel::create([
        'title' => 'T',
        'user_id' => $other->id,
        'priority' => 'medium',
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->put(route('tasks.update', $task), [
            'title' => 'Hacked',
            'description' => null,
            'due_date' => null,
            'priority' => 'low',
        ])
        ->assertForbidden();
});

test('week view returns only tasks due within the current week', function () {
    $user = User::factory()->create();
    $this->travelTo(Carbon::parse('2026-04-09 12:00:00', config('app.timezone')));

    TaskModel::create([
        'user_id' => $user->id,
        'title' => 'Dentro da semana',
        'due_date' => '2026-04-10',
        'priority' => 'medium',
        'status' => 'pending',
    ]);
    TaskModel::create([
        'user_id' => $user->id,
        'title' => 'Fora da semana',
        'due_date' => '2026-03-30',
        'priority' => 'medium',
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->get(route('app', ['view' => 'week']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('tasks', 1)
            ->where('tasks.0.title', 'Dentro da semana')
            ->where('filters.view', 'week'));

    $this->travelBack();
});

test('priority filter returns only matching tasks', function () {
    $user = User::factory()->create();
    TaskModel::create([
        'user_id' => $user->id,
        'title' => 'Alta',
        'priority' => 'high',
        'status' => 'pending',
    ]);
    TaskModel::create([
        'user_id' => $user->id,
        'title' => 'Baixa',
        'priority' => 'low',
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->get(route('app', ['priority' => 'high']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('tasks', 1)
            ->where('tasks.0.title', 'Alta')
            ->where('filters.priority', 'high'));
});

test('status filter returns only matching tasks', function () {
    $user = User::factory()->create();
    TaskModel::create([
        'user_id' => $user->id,
        'title' => 'Feita',
        'priority' => 'medium',
        'status' => TaskModel::STATUS_COMPLETED,
    ]);
    TaskModel::create([
        'user_id' => $user->id,
        'title' => 'Por fazer',
        'priority' => 'medium',
        'status' => TaskModel::STATUS_PENDING,
    ]);

    $this->actingAs($user)
        ->get(route('app', ['status' => 'completed']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('tasks', 1)
            ->where('tasks.0.title', 'Feita')
            ->where('filters.status', 'completed'));
});

test('search filter matches title and description', function () {
    $user = User::factory()->create();
    TaskModel::create([
        'user_id' => $user->id,
        'title' => 'Comprar leite',
        'priority' => 'medium',
        'status' => 'pending',
    ]);
    TaskModel::create([
        'user_id' => $user->id,
        'title' => 'Outra coisa',
        'description' => 'Nota sobre Laravel',
        'priority' => 'medium',
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->get(route('app', ['search' => 'laravel']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('tasks', 1)
            ->where('tasks.0.title', 'Outra coisa'));
});
