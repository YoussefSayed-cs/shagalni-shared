<?php

namespace Shagalni\Shared\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Shagalni\Shared\Enums\AccountStatus;
use Shagalni\Shared\Enums\EmploymentType;
use Shagalni\Shared\Enums\ExperienceLevel;
use Shagalni\Shared\Enums\JobStatus;
use Shagalni\Shared\Enums\RegistrationStatus;
use Shagalni\Shared\Enums\WorkLocationType;

/**
 * @property string $id
 * @property string $company_id
 * @property string $job_category_id
 * @property string $title
 * @property string $description
 * @property string $requirements
 * @property string $location
 * @property EmploymentType $employment_type
 * @property WorkLocationType $work_location_type
 * @property string|null $required_skills
 * @property string|null $salary_min
 * @property string|null $salary_max
 * @property ExperienceLevel|null $experience_level
 * @property Carbon|null $application_deadline
 * @property JobStatus $status
 * @property Carbon|null $archived_at
 * @property Carbon|null $created_at
 * @property-read Company|null $company
 * @property-read JobCategory|null $category
 */
class JobVacancy extends Model
{
    use HasUuids;

    protected $table = 'job_vacancies';

    protected $fillable = [
        'job_category_id',
        'title',
        'description',
        'requirements',
        'location',
        'employment_type',
        'work_location_type',
        'required_skills',
        'salary_min',
        'salary_max',
        'experience_level',
        'application_deadline',
    ];

    protected function casts(): array
    {
        return [
            'employment_type' => EmploymentType::class,
            'work_location_type' => WorkLocationType::class,
            'experience_level' => ExperienceLevel::class,
            'status' => JobStatus::class,
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'application_deadline' => 'date',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * Determine whether the application deadline has passed.
     *
     * Expired is derived from application_deadline.
     * It is not stored as a Job status.
     */
    public function isExpired(): bool
    {
        if ($this->application_deadline === null) {
            return false;
        }

        return Carbon::parse($this->application_deadline)->endOfDay()->isPast();
    }

    /**
     * Single eligibility rule used for public visibility and applying.
     * Active job, valid deadline, Approved company, and active owner.
     */
    public function isAcceptingApplications(): bool
    {
        $status = $this->getAttribute('status');

        if ($status !== JobStatus::Active || $this->isExpired()) {
            return false;
        }

        $company = $this->company;

        $owner = $company?->owner;

        return $company !== null
            && $company->registration_status === RegistrationStatus::Approved
            && $owner !== null
            && $owner->status === AccountStatus::Active;
    }

    /**
     * Query version of isAcceptingApplications() for lists and detail pages.
     *
     * @param  Builder<JobVacancy>  $query
     */
    public function scopePubliclyVisible(Builder $query): void
    {
        $query->where('status', JobStatus::Active->value)
            ->where(function (Builder $query) {
                $query->whereNull('application_deadline')
                    ->orWhereDate('application_deadline', '>=', now()->toDateString());
            })
            ->whereHas('company', function (Builder $company) {
                $company->where('registration_status', RegistrationStatus::Approved->value)
                    ->whereHas('owner', function (Builder $owner) {
                        $owner->where('status', AccountStatus::Active->value);
                    });
            });
    }

    /**
     * Compose title, category, and location search filters.
     *
     * @param  Builder<JobVacancy>  $query
     * @param  array<string, mixed>  $filters
     */
    public function scopeSearchFilters(Builder $query, array $filters): void
    {
        $title = trim((string) ($filters['title'] ?? ''));
        $location = trim((string) ($filters['location'] ?? ''));
        $categoryId = $filters['job_category_id'] ?? null;

        if ($title !== '') {
            $query->whereLike('title', "%{$title}%", false);
        }

        if (is_string($categoryId) && $categoryId !== '') {
            $query->where('job_category_id', $categoryId);
        }

        if ($location !== '') {
            $query->whereLike('location', "%{$location}%", false);
        }
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * @return BelongsTo<JobCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'job_category_id');
    }

    /**
     * @return HasMany<Application, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * @return HasMany<SavedJob, $this>
     */
    public function savedJobs(): HasMany
    {
        return $this->hasMany(SavedJob::class);
    }
}
