<?php

declare(strict_types=1);

use App\Contracts\Auth\DeviceTrustVerifier;
use App\Enums\Systems;
use App\Events\Auth\SuspiciousSessionDetected;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

it('dispatches suspicious session event when device is not trusted', function () {
    Event::fake([SuspiciousSessionDetected::class]);

    /** @var User */
    $user = User::factory()->create();

    $this->mock(DeviceTrustVerifier::class, function (MockInterface $mock) {
        $mock->shouldReceive('resolveDeviceId')->andReturn('fake-device-id');
        $mock->shouldReceive('isTrusted')->andReturn(false);
    });

    $response = $this->actingAs($user)->postJson(
        uri: route('api.v1.users.email.change.request'),
        headers: [
            'User-Agent' => 'Test-Agent',
            'X-Source-System' => Systems::PMS->value,
        ]
    );

    $response->assertStatus(401);

    Event::assertDispatched(SuspiciousSessionDetected::class, function ($event) use ($user) {
        return $event->user->id === $user->id
            && $event->metadata->userAgent === 'Test-Agent';
    });
});
