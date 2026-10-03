<?php

namespace Goralys\Kernel\Data\Enums;

/**
 * This enum is used to differentiate a kernel created for the API and a kernel created for cron jobs. The latter one is
 * much more minimalistic, and thus this enum is used to skip some steps inside the kernel constructor.
 */
enum KernelType
{
    case API;
    case CRON;
}
