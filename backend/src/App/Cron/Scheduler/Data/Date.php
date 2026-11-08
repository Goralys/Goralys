<?php

namespace Goralys\App\Cron\Scheduler\Data;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Goralys\App\Cron\Scheduler\Data\Enums\Month;
use Goralys\Shared\Exception\GoralysRuntimeException;

/**
 * This class is used to more easily handle the scheduler's constraints.
 */
final readonly class Date
{
    public function __construct(
        public Month $month,
        public int $day,
        public int $hour,
        public int $minute
    ) {
    }

    public static function fromDateTime(DateTimeInterface $dt): self
    {
        return new self(
            Month::from((int) $dt->format('n')),
            (int) $dt->format('j'),
            (int) $dt->format('G'),
            (int) $dt->format('i'),
        );
    }

    /**
     * Returns the current date using a strictly typed {@see Date} object.
     * @return self
     */
    public static function now(): self
    {
        return self::fromDateTime(new DateTimeImmutable());
    }

    /**
     * Every minute from now - $before to now + $after (inclusive),
     * computed from a single instant so the window can't shift mid-call.
     * @return self[]
     * @throws GoralysRuntimeException
     */
    public static function window(int $before, int $after): array
    {
        $now = new DateTimeImmutable();
        $dates = [];

        for ($k = -$before; $k < $after; $k++) {
            try {
                $dates[] = self::fromDateTime($now->modify("$k minutes"));
            } catch (Exception $e) {
                throw new GoralysRuntimeException("Failed to modify date: {$e->getMessage()}");
            }
        }

        return $dates;
    }
}
