<?php

namespace Abdulbasset\NssmPhp;

use Abdulbasset\NssmPhp\Executors\Executor;

class NssmSet
{
    public function __construct(protected array $command, protected Executor $executor)
    {

    }

    public function getCommand(): array
    {
        return $this->command;
    }

    public function displayName(string $name): self
    {
        $this->executor->run([
            ...$this->command,
            'DisplayName',
            $name,
        ]);

        return $this;
    }

    public function description(string $text): self
    {
        $this->executor->run([
            ...$this->command,
            'Description',
            $text,
        ]);

        return $this;
    }

    public function startup(Startup $type): self
    {
        $this->executor->run([
            ...$this->command,
            'Start',
            $type->value,
        ]);

        return $this;
    }

    public function appDirectory(string $path): self
    {
        $this->executor->run([
            ...$this->command,
            'AppDirectory',
            $path,
        ]);

        return $this;
    }

    public function output(string $path): self
    {
        $this->executor->run([
            ...$this->command,
            'AppStdout',
            $path,
        ]);

        return $this;
    }

    public function error(string $path): self
    {
        $this->executor->run([
            ...$this->command,
            'AppStderr',
            $path,
        ]);

        return $this;
    }
}
