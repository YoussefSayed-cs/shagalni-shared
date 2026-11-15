<?php

namespace Shagalni\Shared\Enums;

enum JobStatus: string
{
    case Active = 'active';
    case Closed = 'closed';
    case Archived = 'archived';
}
