<?php

namespace Abdulbasset\NssmPhp\Executors;

interface Executor
{
    public function run(array $command): string;
}