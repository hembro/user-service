<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\Enums\Systems;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Laravel\Passport\Passport;
use RuntimeException;

final readonly class RevokeSystemTokens
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(User $user, Systems $system): void
    {
        $clients = config('services.passport.frontend_clients');

        $clientId = $clients[$system->value]['client_id'] ?? null;

        if ($clientId === null) {
            throw new RuntimeException("Passport Client ID not configured for system: {$system->name}");
        }

        $tokensTable = Passport::token()->getTable();
        $refreshTokensTable = Passport::refreshToken()->getTable();

        $this->db->transaction(
            callback: function () use ($user, $clientId, $tokensTable, $refreshTokensTable) {

                $this->db->table($tokensTable)
                    ->where('user_id', $user->id)
                    ->where('client_id', $clientId)
                    ->where('revoked', false)
                    ->update(['revoked' => true]);

                $this->db->table($refreshTokensTable)
                    ->whereIn('access_token_id', function ($query) use ($tokensTable, $user, $clientId) {
                        $query->select('id')
                            ->from($tokensTable)
                            ->where('user_id', $user->id)
                            ->where('client_id', $clientId);
                    })
                    ->where('revoked', false)
                    ->update(['revoked' => true]);
            }
        );
    }
}
