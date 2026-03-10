<?php

namespace Abdulbasset\NssmPhp\Executors;

use Abdulbasset\NssmPhp\Exceptions\NssmException;
use Symfony\Component\Process\Process;

class FakeExecutor implements Executor
{
    public array $commands = [];
    protected array $mockedResponses = [];

    public function __construct(protected string $nssm)
    {

    }

    public function returnFor(string $commandContains, string $output): void
    {
        $this->mockedResponses[$commandContains] = $output;
    }

    public function run(array $command): string
    {
        $this->commands[] = [$this->nssm, ...$command];
        $commandString = implode(' ', $command);

        // If a test defined a mock response for this command, return it
        foreach ($this->mockedResponses as $target => $output) {
            if (str_contains($commandString, $target)) {
                return $output;
            }
        }

        return $commandString;
    }
}