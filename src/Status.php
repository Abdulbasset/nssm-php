<?php

namespace Abdulbasset\NssmPhp;

enum Status: string
{
    case Running = 'SERVICE_RUNNING';
    case Stopped = 'SERVICE_STOPPED';
    case StartPending = 'SERVICE_START_PENDING';
    case StopPending = 'SERVICE_STOP_PENDING';
    case Paused = 'SERVICE_PAUSED';
    case NotFound = 'SERVICE_NOT_FOUND';

    public function running(): bool
    {
        return $this === self::Running;
    }

    public function pending(): bool
    {
        return in_array($this, [self::StartPending, self::StopPending]);
    }

    public function exists(): bool
    {
        return $this !== self::NotFound;
    }
}
