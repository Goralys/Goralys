<?php

namespace Goralys\Core\Audit\Data;

use JsonSerializable;

/**
 * Represents a list (collection) of {@see AuditAction}s.
 */
final class AuditActionsCollection implements JsonSerializable
{
    /* @var AuditAction[] */
    public array $actions = [];

    /**
     * Adds a new action to the collection.
     * @param AuditAction $newAction The action to add.
     * @return void
     */
    public function addAction(AuditAction $newAction): void
    {
        $this->actions = [...$this->actions, $newAction];
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
