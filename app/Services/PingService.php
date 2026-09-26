<?php

namespace App\Services;

use App\Models\MonitoredHost;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class PingService
{
    public function check(MonitoredHost $host): PingResult
    {
        if (! filter_var($host->ip_address, FILTER_VALIDATE_IP)) {
            return new PingResult(false, null, 100, 'invalid_ip', 'The stored target is not a valid IP address.');
        }$binary = trim((string) config('monitoring.ping_binary', '/bin/ping'));
        if (! is_executable($binary)) {
            return new PingResult(false, null, 100, 'binary_unavailable', 'Ping binary is unavailable or not executable.');
        }$ipv6 = filter_var($host->ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        $timeout = max(1, (int) ceil($host->timeout_ms / 1000));
        $count = max(1, (int) $host->retry_count);
        $process = new Process([$binary, $ipv6 ? '-6' : '-4', '-n', '-c', (string) $count, '-W', (string) $timeout, $host->ip_address], null, ['LC_ALL' => 'C']);
        $process->setTimeout(($timeout * $count) + 2);
        try {
            $process->run();
        } catch (ProcessTimedOutException) {
            return new PingResult(false, null, 100, 'timeout', 'Ping process timed out.');
        } catch (\Throwable $e) {
            return new PingResult(false, null, 100, 'process_error', $e->getMessage());
        }$output = $process->getOutput().' '.$process->getErrorOutput();
        preg_match('/([\d.]+)% packet loss/i', $output, $loss);
        preg_match('/(?:rtt|round-trip)[^=]*=\s*[\d.]+\/([\d.]+)\//i', $output, $latency);
        $packetLoss = isset($loss[1]) ? (float) $loss[1] : ($process->isSuccessful() ? 0.0 : 100.0);
        $latencyMs = isset($latency[1]) ? (float) $latency[1] : null;

        return new PingResult($packetLoss < 100 && $latencyMs !== null, $latencyMs, $packetLoss, $process->isSuccessful() ? null : 'unreachable', $process->isSuccessful() ? null : (trim($process->getErrorOutput()) ?: 'No ICMP response received.'));
    }
}
