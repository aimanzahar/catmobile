<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PushTokenGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $id,
        public string $token,
        public ?string $platform = null,
    ) {}
}
