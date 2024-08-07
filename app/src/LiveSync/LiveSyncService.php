<?php

namespace App\LiveSync;

use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\User\Model\User;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

/**
 * Main service which handles all aspects of the mercure-based live sync.
 *
 * CONSUMERS need to call {@see LiveSyncService::getSubscriberJwtCookie()}.
 *
 * To PUBLISH a message, one needs to call {@see LiveSyncService::publish()}.
 */
class LiveSyncService
{
    public function __construct(
        private readonly string       $jwtPrivateSigningKey,
        private readonly HubInterface $publisher,
        protected bool                $secureCookie = true
    )
    {
        $this->secureCookie = $_ENV['APP_ENV'] === 'prod';
    }

    public static function getBaseJwtPayload(): array
    {
        return [
            "iss" => "Degree 5.0 App",
            "iat" => time(), // issued at
            "nbf" => time() - 5 * 60, // not before,
            "exp" => time() + 60 * 60 * 6, // expiration in 6 hours,
        ];
    }

    private static function buildMercureTopicIdentifier(ExercisePhaseTeam $exercisePhaseTeam): string
    {
        return 'exercisePhaseTeam-' . $exercisePhaseTeam->getId();
    }

    private static function buildPresenceTopicIdentifier(ExercisePhaseTeam $exercisePhaseTeam): string
    {
        return '/.well-known/mercure/subscriptions/' . self::buildMercureTopicIdentifier($exercisePhaseTeam) . '/{subscription}';
    }

    private static function buildSubscriptionAPIEndpoint(ExercisePhaseTeam $exercisePhaseTeam): string
    {
        return '/.well-known/mercure/subscriptions/' . self::buildMercureTopicIdentifier($exercisePhaseTeam);
    }

    public function getSubscriberJwtCookie(User $user, ExercisePhaseTeam $exercisePhaseTeam): Cookie
    {
        $exercisePhaseTeamTopicIdentifiers = [];
        $exercisePhaseTeamTopicIdentifiers[] = self::buildMercureTopicIdentifier($exercisePhaseTeam);
        // we also need to enable access to the subscription topic
        $exercisePhaseTeamTopicIdentifiers[] = self::buildPresenceTopicIdentifier($exercisePhaseTeam);
        // we also need to enable access to the subscription API
        $exercisePhaseTeamTopicIdentifiers[] = self::buildSubscriptionAPIEndpoint($exercisePhaseTeam);

        $payload = self::getBaseJwtPayload();
        $payload['mercure'] = [
            "subscribe" => $exercisePhaseTeamTopicIdentifiers,
            "payload" => [
                "user" => [
                    "id" => $user->getId(),
                    "name" => $user->getUsername()
                ]
            ]
        ];

        $jwt = JWT::encode($payload, $this->jwtPrivateSigningKey, 'HS256');
        // 6 hours
        $expirationTime = time() + 6 * 3600;

        return new Cookie('mercureAuthorization', $jwt, $expirationTime, '/', null, $this->secureCookie, true);
    }

    public function getClientSideLiveSyncConfig(ExercisePhaseTeam $exercisePhaseTeam): array
    {
        return [
            'topics' => [
                'solution' => self::buildMercureTopicIdentifier($exercisePhaseTeam),
                'presence' => self::buildPresenceTopicIdentifier($exercisePhaseTeam)
            ],
            'mercureEndpoint' => '/.well-known/mercure',
            'subscriptionsEndpoint' => self::buildSubscriptionAPIEndpoint($exercisePhaseTeam),
            'teamMembers' => array_map(function (User $member) {
                return [
                    'id' => $member->getId(),
                    'name' => $member->getUsername(),
                    'connectionState' => 'UNKNOWN'
                ];
            }, $exercisePhaseTeam->getMembers()->toArray())
        ];
    }

    public function publish(ExercisePhaseTeam $exercisePhaseTeam, array $data): void
    {
        $update = new Update(
            self::buildMercureTopicIdentifier($exercisePhaseTeam),
            json_encode($data),
            true
        );
        $this->publisher->publish($update);
    }
}
