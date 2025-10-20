<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Process;

class SystemService
{
    public function getSystemInfo(): array
    {
        return [
            'hostname' => $this->getHostname(),
            'os' => $this->getOSVersion(),
            'uptime' => $this->getUptime(),
            'load_average' => $this->getLoadAverage(),
        ];
    }

    public function getCpuUsage(): float
    {
        $result = Process::run('top -bn1 | grep "Cpu(s)" | sed "s/.*, *\\([0-9.]*\\)%* id.*/\\1/" | awk \'{print 100 - $1}\'');

        if ($result->successful()) {
            return (float) trim($result->output());
        }

        return 0.0;
    }

    public function getMemoryUsage(): array
    {
        $result = Process::run('free -b | grep Mem');

        if ($result->successful()) {
            $parts = preg_split('/\s+/', trim($result->output()));

            return [
                'total' => (int) ($parts[1] ?? 0),
                'used' => (int) ($parts[2] ?? 0),
                'free' => (int) ($parts[3] ?? 0),
                'percentage' => $parts[1] > 0 ? round(($parts[2] / $parts[1]) * 100, 2) : 0,
            ];
        }

        return ['total' => 0, 'used' => 0, 'free' => 0, 'percentage' => 0];
    }

    public function getDiskUsage(): array
    {
        $result = Process::run('df -B1 / | tail -1');

        if ($result->successful()) {
            $parts = preg_split('/\s+/', trim($result->output()));

            return [
                'total' => (int) ($parts[1] ?? 0),
                'used' => (int) ($parts[2] ?? 0),
                'free' => (int) ($parts[3] ?? 0),
                'percentage' => (float) str_replace('%', '', $parts[4] ?? '0'),
            ];
        }

        return ['total' => 0, 'used' => 0, 'free' => 0, 'percentage' => 0];
    }

    protected function getHostname(): string
    {
        $result = Process::run('hostname');
        return $result->successful() ? trim($result->output()) : 'Unknown';
    }

    protected function getOSVersion(): string
    {
        $result = Process::run('cat /etc/os-release | grep PRETTY_NAME | cut -d "=" -f 2 | tr -d \'"\'');
        return $result->successful() ? trim($result->output()) : 'Unknown';
    }

    protected function getUptime(): string
    {
        $result = Process::run('uptime -p');
        return $result->successful() ? trim($result->output()) : 'Unknown';
    }

    protected function getLoadAverage(): array
    {
        $result = Process::run('cat /proc/loadavg');

        if ($result->successful()) {
            $parts = explode(' ', trim($result->output()));

            return [
                '1min' => (float) ($parts[0] ?? 0),
                '5min' => (float) ($parts[1] ?? 0),
                '15min' => (float) ($parts[2] ?? 0),
            ];
        }

        return ['1min' => 0, '5min' => 0, '15min' => 0];
    }

    public function executeCommand(string $command): array
    {
        $result = Process::run($command);

        return [
            'success' => $result->successful(),
            'output' => $result->output(),
            'error' => $result->errorOutput(),
            'exit_code' => $result->exitCode(),
        ];
    }
}

