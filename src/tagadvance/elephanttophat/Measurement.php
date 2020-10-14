<?php

declare(strict_types=1);

namespace tagadvance\elephanttophat;

class Measurement
{
	public const UNIT_PERCENT = '%';

	private $value;
	private ?string $unit;

	public function __construct($value, string $unit = null)
	{
		$this->value = $value;
		$this->unit = $unit;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @return string|null
	 */
	public function getUnit(): ?string
	{
		return $this->unit;
	}
}
