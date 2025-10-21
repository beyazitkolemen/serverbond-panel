<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class BaseServerBondService
{
    protected string $baseDir = '/opt/serverbond-agent/scripts';
    protected string $libPath = '/opt/serverbond-agent/scripts/lib.sh';
    protected string $logPath = '/opt/serverbond-agent/logs/script_exec.log';

    /**
     * Execute a serverbond script with parameters
     */
    protected function executeScript(string $scriptPath, array $params = []): array
    {
        $fullPath = $this->baseDir . '/' . $scriptPath;
        
        if (!file_exists($fullPath)) {
            return [
                'status' => 'error',
                'message' => "Script not found: {$scriptPath}",
                'data' => null
            ];
        }

        // Build command with parameters
        $command = "bash {$fullPath}";
        
        foreach ($params as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $command .= " --{$key}=" . escapeshellarg((string)$value);
        }

        try {
            Log::info("Executing serverbond script", [
                'script' => $scriptPath,
                'params' => $params,
                'command' => $command
            ]);

            $result = Process::run($command);
            
            $output = $result->output();
            $errorOutput = $result->errorOutput();
            $exitCode = $result->exitCode();

            // Try to parse JSON output
            $decoded = json_decode($output, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            // If not JSON, return raw output
            return [
                'status' => $exitCode === 0 ? 'success' : 'error',
                'message' => $output ?: $errorOutput,
                'data' => null
            ];

        } catch (Exception $e) {
            Log::error("Failed to execute serverbond script", [
                'script' => $scriptPath,
                'error' => $e->getMessage()
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Validate required parameters
     */
    protected function validateParams(array $params, array $required): void
    {
        foreach ($required as $key) {
            if (!array_key_exists($key, $params)) {
                throw new \InvalidArgumentException("Required parameter '{$key}' is missing");
            }
        }
    }

    /**
     * Get script path for a given script name
     */
    protected function getScriptPath(string $category, string $script): string
    {
        return "{$category}/{$script}.sh";
    }
}