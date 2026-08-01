<?php

namespace App\Platform\Support;

use Illuminate\Support\Carbon;

/**
 * One line in the Hub's activity feed. Tools build these; the Hub decides how
 * they look and which tool badge they carry.
 */
final readonly class ActivityItem
{
    public function __construct(
        public string $text,
        public Carbon $at,
        public ?Tool $tool = null,
    ) {}

    /** Returns a copy tagged with the tool that produced it. */
    public function for(Tool $tool): self
    {
        return new self($this->text, $this->at, $tool);
    }
}
