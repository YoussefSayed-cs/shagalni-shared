<?php

namespace Shagalni\Shared\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case CompanyOwner = 'company_owner';
    case JobSeeker = 'job_seeker';
}
