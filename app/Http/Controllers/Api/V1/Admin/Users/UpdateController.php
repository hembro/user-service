<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Events\AdminUpdatedUser;
use Illuminate\Support\Facades\Request;

final class UpdateController
{
    public function __construct(
        private AdminUpdatedUser $action
    ) {}

    public function __invoke(Request $request)
    {
        //
    }
}
