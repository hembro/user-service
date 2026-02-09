<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Admin\Users;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Admin\Users\IndexRequest;

final readonly class IndexUserDTO
{
    public function __construct(
        public Systems $system,
        public int $page,
        public int $perPage,
        public ?string $search,
        public ?string $role,
        public ?string $status,
        public ?string $sort,
        public ?string $trashed
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
            trashed: $request->query('trashed'),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            system: Systems::from($data['system']),
            page: $data['page'] ?? null,
            perPage: $data['per_page'] ?? null,
            search: $data['search'] ?? null,
            role: $data['role'] ?? null,
            status: $data['status'] ?? null,
            sort: $data['sort'] ?? null,
            trashed: $data['trashed'] ?? null
        );
    }
}
