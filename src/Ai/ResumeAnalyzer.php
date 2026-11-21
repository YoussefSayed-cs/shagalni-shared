<?php

namespace Shagalni\Shared\Ai;

interface ResumeAnalyzer
{
    public function analyze(string $resumeText, array $job): AnalysisResult;
}