<?php

namespace Shagalni\Shared\Ai;

final class AnalysisResult
{
    public function __construct(
        public readonly int $score,
        public readonly string $feedback
    ) {}
}