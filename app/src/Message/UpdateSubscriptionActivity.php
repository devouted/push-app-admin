<?php

namespace App\Message;

class UpdateSubscriptionActivity
{
    public function __construct(
        public readonly string $consumerId,
        public readonly string $channelId,
    ) {}
}
