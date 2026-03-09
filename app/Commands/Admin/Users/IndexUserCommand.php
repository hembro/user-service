<?php

declare(strict_types=1);

namespace App\Commands\Admin\Users;

use App\Http\Requests\Api\V1\Admin\Users\IndexRequest;
use jeremyaliparo\Foundation\Enums\System;

final readonly class IndexUserCommand
{
    public function __construct(
        public System $system,
        public int $page,
        public int $perPage,
        public ?string $search,
        public ?string $role,
        public ?string $status,
        public ?string $sort,
        public ?string $deleted
    ) {}

    public static function fromRequest(IndexRequest $request): self
    {
        return new self(
            system: $request->attributes->get('system'),
            page: $request->integer('page', 1),
            perPage: $request->integer('per_page', 10),
            search: $request->query('search'),
            role: $request->query('role'),
            status: $request->query('status'),
            sort: $request->query('sort'),
            deleted: $request->query('deleted'),
        );
    }
}
