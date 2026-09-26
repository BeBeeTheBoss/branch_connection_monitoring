<?php

namespace App\Services;

readonly class PingResult
{
    public function __construct(public bool $reachable, public ?float $latencyMs, public float $packetLoss, public ?string $errorCode = null, public ?string $errorMessage = null) {}
}
