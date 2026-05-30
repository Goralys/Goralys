<?php

namespace Goralys\Tests\Fakes;

use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;

class FakeGoralysLogger implements LoggerInterface
{
    public array $logs = [];

    public function info(LoggerInitiator $initiator, string $message): void
    {
        $this->log('INFO', $initiator, $message);
    }

    public function debug(LoggerInitiator $initiator, string $message): void
    {
        $this->log('DEBUG', $initiator, $message);
    }

    public function warning(LoggerInitiator $initiator, string $message): void
    {
        $this->log('WARNING', $initiator, $message);
    }

    public function error(LoggerInitiator $initiator, string $message): void
    {
        $this->log('ERROR', $initiator, $message);
    }

    public function fatal(LoggerInitiator $initiator, string $message): void
    {
        $this->log('FATAL', $initiator, $message);
    }

    private function log(string $level, LoggerInitiator $initiator, string $message): void
    {
        $this->logs = [...$this->logs, [
            'level' => $level,
            'initiator' => $initiator,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
        ]];
    }

    public function reset(): void
    {
        $this->logs = [];
    }

    public function rotate(): void
    {
    }
}
