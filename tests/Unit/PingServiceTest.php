<?php

namespace Tests\Unit;

use App\Models\MonitoredHost;
use App\Services\PingService;
use PHPUnit\Framework\TestCase;

class PingServiceTest extends TestCase
{
    public function test_rejects_invalid_stored_target_before_process_execution(): void
    {
        $host = new MonitoredHost(['ip_address' => '127.0.0.1; rm -rf /']);
        $result = (new PingService)->check($host);
        $this->assertFalse($result->reachable);
        $this->assertSame('invalid_ip', $result->errorCode);
    }
}
