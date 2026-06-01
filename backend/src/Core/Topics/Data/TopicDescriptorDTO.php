<?php

/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Goralys\Core\Topics\Data;

/**
 * DTO for topic metadata extracted from filenames.
 */
final readonly class TopicDescriptorDTO
{
    /**
     * @param string $name The name of the topic.
     * @param string $code The code of the topic.
     */
    public function __construct(
        public string $name,
        public string $code,
    ) {
    }
}
