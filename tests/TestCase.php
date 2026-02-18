<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PragmaRX\Google2FA\Google2FA;

abstract class TestCase extends BaseTestCase
{
    public ?User $user = null;
    public ?User $admin = null;
    public ?Google2FA $google2fa = null;
    public ?string $userSecret = null;
}
