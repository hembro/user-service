<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Admin\Users;

use App\Enums\Systems;
use Illuminate\Http\Request;

final readonly class IndexUserDTO
{
    public function __construct(
        public Systems $system,
        public int $page,
        public int $perPage,
        public ?string $search,
        public ?string $role,
        public ?string $status,
        public ?string $sort
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            system: Systems::from($request->validated('system')),
            page: $request->integer('page', 1),
            perPage: $request->integer('per_page', 10),
            search: $request->query('search'),
            role: $request->query('role'),
            status: $request->query('status'),
            sort: $request->query('sort'),
        );
    }

    public function toArray(): array
    {
        return [
            'sys' => $this->system->value,
            'pg' => $this->page,
            'pp' => $this->perPage,
            'q' => $this->search,
            'role' => $this->role,
            'st' => $this->status,
            'srt' => $this->sort,
        ];
    }

    public function generateCacheKey(): string
    {
        return md5(json_encode($this->toArray()));
    }
}
