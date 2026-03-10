<?php

namespace Abdulbasset\NssmPhp;

use Abdulbasset\NssmPhp\Executors\Executor;
use Abdulbasset\NssmPhp\Executors\SymfonyProcessExecutor;

class Nssm
{
    protected string $binary;

    public function __construct(
        protected ?string $service = null,
        protected string $nssm = 'nssm',
        protected ?Executor $executor = null
    )
    {
        $this->executor ??= new SymfonyProcessExecutor($this->nssm);
    }

    public function service(string $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function nssm(string $nssm): self
    {
        $this->nssm = $nssm;

        return $this;
    }

    public function bin(string $binary): self
    {
        $this->binary = $binary;

        return $this;
    }

    public function install(...$args): self
    {
        $this->executor->run([
            'install',
            $this->service,
            $this->binary,
            ...$args,
        ]);;

        return $this;
    }

    public function remove(): self
    {
        $this->executor->run([
            'remove',
            $this->service,
            'confirm',
        ]);

        return $this;
    }

    public function set(\Closure $callback): self
    {
        $set = new NssmSet([
            'set',
            $this->service,
        ], $this->executor);

        $callback->call($this, $set);

        return $this;
    }

    public function start(): self
    {
        $this->executor->run([
            'start',
            $this->service,
        ]);

        return $this;
    }

    public function stop(): self
    {
        $this->executor->run([
            'stop',
            $this->service,
        ]);

        return $this;
    }

    public function restart(): self
    {
        $this->executor->run([
            'restart',
            $this->service,
        ]);

        return $this;
    }

    public function status(): ?Status
    {
        $status = $this->executor->run([
            'status',
            $this->service,
        ]);

        return Status::tryFrom($status);
    }
}
