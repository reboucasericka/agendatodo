<?php

use App\Models\Meeting;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests cannot access meetings', function () {
    $this->get(route('meetings.index'))->assertRedirect(route('login'));
});

test('user sees only own meetings', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Meeting::create([
        'user_id' => $user->id,
        'title' => 'Minha',
        'meeting_date' => now()->addDay()->toDateString(),
        'status' => Meeting::STATUS_SCHEDULED,
    ]);
    Meeting::create([
        'user_id' => $other->id,
        'title' => 'Outra',
        'meeting_date' => now()->addDay()->toDateString(),
        'status' => Meeting::STATUS_SCHEDULED,
    ]);

    $this->actingAs($user)
        ->get(route('meetings.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Meetings/Index')
            ->has('meetings', 1)
            ->where('meetings.0.title', 'Minha'));
});

test('user can create a meeting', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('meetings.store'), [
            'title' => 'Kick-off',
            'meeting_date' => '2026-06-12',
            'meeting_time' => '10:00',
            'status' => Meeting::STATUS_SCHEDULED,
            'duration' => '30min – 1 hora',
            'platform' => 'Zoom',
            'participants_text' => 'Ana, Bruno',
            'redirect_tab' => 'all',
        ])
        ->assertRedirect();

    $m = Meeting::first();
    expect($m->title)->toBe('Kick-off')
        ->and($m->participants)->toBe(['Ana', 'Bruno']);
});

test('user cannot update another users meeting', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $meeting = Meeting::create([
        'user_id' => $other->id,
        'title' => 'X',
        'meeting_date' => now()->toDateString(),
        'status' => Meeting::STATUS_SCHEDULED,
    ]);

    $this->actingAs($user)
        ->put(route('meetings.update', $meeting), [
            'title' => 'Hacked',
            'meeting_date' => now()->toDateString(),
            'status' => Meeting::STATUS_SCHEDULED,
        ])
        ->assertForbidden();
});
