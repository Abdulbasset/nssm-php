<?php

namespace Abdulbasset\NssmPhp;

use Abdulbasset\NssmPhp\Executors\Executor;
use DateInterval;

class NssmRotation
{
    public function __construct(protected array $command, protected Executor $executor)
    {

    }

    public function getCommand(): array
    {
        return $this->command;
    }

    public function enable(): self
    {
        $this->executor->run([
            ...$this->command,
            'AppRotateFiles',
            '1',
        ]);

        return $this;
    }

    public function stop(): self
    {
        $this->executor->run([
            ...$this->command,
            'AppRotateFiles',
            '0',
        ]);

        return $this;
    }

    public function online(bool $enable = true): self
    {
        $this->executor->run([
            ...$this->command,
            'AppRotateOnline',
            $enable ? '1' : '0',
        ]);

        return $this;
    }

    public function everySeconds(int|DateInterval $seconds): self
    {
        if ($seconds instanceof DateInterval) {
            // Convert DateInterval to total seconds
            $reference = new \DateTimeImmutable();
            $endTime = $reference->add($seconds);
            $seconds = $endTime->getTimestamp() - $reference->getTimestamp();
        }

        $this->executor->run([
            ...$this->command,
            'AppRotateSeconds',
            $seconds,
        ]);

        return $this;
    }

    public function everyBytes(int $size): self
    {
        $this->executor->run([
            ...$this->command,
            'AppRotateBytes',
            $size,
        ]);

        return $this;
    }

    public function reset(): self
    {
        $this->command = [
            'reset',
            $this->command[1]
        ];

        $this->executor->run([...$this->command, 'AppRotateFiles']);
        $this->executor->run([...$this->command, 'AppRotateOnline']);
        $this->executor->run([...$this->command, 'AppRotateSeconds']);
        $this->executor->run([...$this->command, 'AppRotateBytes']);

        return $this;
    }
}
