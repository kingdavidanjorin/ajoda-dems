<?php

declare(strict_types=1);

namespace App\Enums;

enum CommunicationChannel: string
{
    case PhoneCall = 'phone_call';
    case Email = 'email';
    case WhatsApp = 'whatsapp';
    case Sms = 'sms';
    case Meeting = 'meeting';
    case PortalMessage = 'portal_message';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PhoneCall => 'Phone Call',
            self::Email => 'Email',
            self::WhatsApp => 'WhatsApp',
            self::Sms => 'SMS',
            self::Meeting => 'Meeting',
            self::PortalMessage => 'Portal Message',
            self::Other => 'Other',
        };
    }
}