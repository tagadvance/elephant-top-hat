<?php

declare(strict_types=1);

namespace tagadvance\elephanttophat;

/**
 * One value from a `top` summary line, paired with the scale label `top` printed it under.
 * Values are kept exactly as `top` rendered them rather than converted to a common unit.
 */
class Measurement
{
    public const UNIT_PERCENT = '%';

    private $value;
    private ?string $unit;

    /**
     * @param string|null $unit The scale `top` reported the value in, or null for a
     *     dimensionless value such as a task count, a load average or an uptime.
     */
    public function __construct($value, ?string $unit = null)
    {
        $this->value = $value;
        $this->unit = $unit;
    }

    /**
     * The value as {@see Top::parse()} produced it: a `DateTimeImmutable` for `time`, an `int`
     * for the user and task counts, and a string for everything else — decimals stay strings.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * `Measurement::UNIT_PERCENT` for the `cpu_*` shares, the `KiB`/`MiB`/`GiB`/… label from
     * `top`'s own memory lines for the `memory_*` and `swap_*` values, and null for the rest.
     */
    public function getUnit(): ?string
    {
        return $this->unit;
    }
}
