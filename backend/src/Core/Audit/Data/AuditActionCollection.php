<?php

namespace Goralys\Core\Audit\Data;

use JsonSerializable;

/**
 * Represents a list (collection) of {@see AuditAction}s.
 */
final class AuditActionCollection implements JsonSerializable
{
    /**
     * @param array $actions The actions contained inside the collection.
     */
    public function __construct(
        private(set) array $actions
    ) {
    }

    /**
     * Adds new action(s) to the collection.
     * @param AuditAction $first The first action to add.
     * @param AuditAction ...$_ Other actions to add (optionnal).
     * @return void
     */
    public function addActions(AuditAction $first, AuditAction ...$_): void
    {
        $this->actions = [...$this->actions, $first, ...$_];
    }

    /**
     * Transforms all the actions inside the collection into JSON arrays.
     * @return array The comprehensive list of all actions from the collection.
     */
    public function jsonSerialize(): array
    {
        return $this->actions;
    }
}
