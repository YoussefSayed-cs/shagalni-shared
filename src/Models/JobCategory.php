<?php

namespace Shagalni\Shared\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $name
 */
class JobCategory extends Model
{
    use HasUuids;

    protected $table = 'job_categories';

    protected $fillable = ['name'];

    /**
     * @return HasMany<JobVacancy, $this>
     */
    public function jobVacancies(): HasMany
    {
        return $this->hasMany(JobVacancy::class);
    }
}
