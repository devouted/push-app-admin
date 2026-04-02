<?php

namespace App\Message;

class SendPushNotification
{
    public function __construct(
        public readonly string $notificationId,
    ) {}
}
