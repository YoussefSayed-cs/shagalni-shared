<?php

namespace Shagalni\Shared\Enums;

enum ApplicationStatus: string
{
    case Pending = 'pending';
    case UnderReview = 'under_review';
    case Shortlisted = 'shortlisted';
    case Interview = 'interview';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';

    
    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Accepted,
            self::Rejected,
            self::Withdrawn,
        ]);
    }

    public function next(): ?self
    {
        return match ($this) {
            self::Pending => self::UnderReview,
            self::UnderReview => self::Shortlisted,
            self::Shortlisted => self::Interview,
            self::Interview => self::Accepted,
            default => null,
        };
    }

    public function canTransitionTo(self $to): bool
    {
        if ($this->isTerminal()) {
            return false;
        }

        return $to === self::Rejected || $to === $this->next();
    }
}
