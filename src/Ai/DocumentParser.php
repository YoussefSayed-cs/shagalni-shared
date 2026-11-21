<?php

namespace Shagalni\Shared\Ai;

use Shagalni\Shared\Models\ResumeSnapshot;

interface DocumentParser
{
    public function extract(ResumeSnapshot $snapshot): string;
}