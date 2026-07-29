<?php

declare(strict_types=1);

namespace App\Enums;

enum CommunicationDirection: string
{
    case Incoming = 'incoming';
    case Outgoing = 'outgoing';
    case Internal = 'internal';

    public function label(): string
    {
        return match ($this) {
            self::Incoming => 'Incoming',
            self::Outgoing => 'Outgoing',
            self::Internal => 'Internal',
        };
    }
}