<?php

namespace Goralys\App\Context;

final class Context
{
    /**
     * This variable is used to determine the context of the app in the global scope.
     * @var ?Context
     */
    public static ?self $CURRENT = null;

    public function __construct(
        public AppContext $app,
        public JobsContext $jobs,
    ) {
    }
}
