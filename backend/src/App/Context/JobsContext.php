<?php

namespace Goralys\App\Context;

final class JobsContext
{
    /**
     * @param bool $runOnce Wether jobs should be ran once or for each school.
     */
    public function __construct(
        public bool $runOnce = false
    ) {
    }
}
