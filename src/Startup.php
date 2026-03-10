<?php

namespace Abdulbasset\NssmPhp;

enum Startup: string
{
    case Automatic = 'SERVICE_AUTO_START';
    case Delayed = 'SERVICE_DELAYED_AUTO_START';
    case Manual = 'SERVICE_DEMAND_START';
    case Disabled = 'SERVICE_DISABLED';
}
