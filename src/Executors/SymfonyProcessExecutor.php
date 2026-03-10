<?php

namespace Abdulbasset\NssmPhp\Executors;

use Abdulbasset\NssmPhp\Exceptions\NssmException;
use Symfony\Component\Process\Process;

class SymfonyProcessExecutor implements Executor
{
    public function __construct(protected string $nssm)
    {

    }

    public function run(array $command): string
    {
        $process = new Process([
            $this->nssm,
            ...$command,
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            $output = $this->convertOutputToUtf8($process->getErrorOutput());

            return match (true) {
                str_contains($output, "Can't open service") => 'SERVICE_NOT_FOUND',
                default => throw new NssmException('NSSM Command Failed: ' . $output),
            };
        }

        return $this->convertOutputToUtf8($process->getOutput());
    }

    protected function convertOutputToUtf8(string $output): string
    {
        if (str_contains($output, "\x00")) {
            // Convert from UTF-16LE to UTF-8 and trim whitespace/newlines
            $output = mb_convert_encoding($output, 'UTF-8', 'UTF-16LE');
        }

        return trim($output);
    }
}