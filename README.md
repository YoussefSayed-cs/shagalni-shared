# Shagalni Shared

Shared Laravel models and enums used by the Shagalni applications.

## Required route names

Every app that sends the account lifecycle notifications must define the
`account-deletion.cancel` and `account-reactivation.confirm` route names.

## Application notification events

- `ApplicationSubmitted` is dispatched by the Portal after its application-submission transaction commits. The shared listener notifies the owning Company Owner.
- `ApplicationStatusUpdated` and `ApplicationWithdrawn` are dispatched by the shared `ApplicationStatusHistoryObserver` after the history transaction commits. The observer ignores initial history rows. The status-update listener notifies an eligible Job Seeker; the withdrawal listener notifies the owning Company Owner.

## AI analysis contracts

- `Shagalni\Shared\Ai\DocumentParser` and `Shagalni\Shared\Ai\ResumeAnalyzer` are interfaces for extracting resume text and analyzing it against a job. `AnalysisResult` carries the score and feedback.
- `UnsupportedResumeException` represents a permanent input failure and is never retried. Any other exception is transient and is retried up to three attempts total.
- `ProcessResumeAnalysis` runs on the `ai` queue. `SharedServiceProvider` registers `QueueResumeAnalysis` for `ApplicationSubmitted`; the listener creates the pending analysis and dispatches the job.
- Any application running a worker for `ai` **must bind both interfaces** in its container. An application that only dispatches the job, such as Backoffice, must not bind them and must not run the `ai` queue.
- The job sends only job context and resume text to the analyzer, never applicant profile data, and it never changes the Application status.
- After changing this package, bump and tag its version, then run `composer update shagalni/shared` in both applications, following the deployment order below.

## Local development

The applications may use a Composer path repository pointing to a sibling
checkout of this package. This is convenient during coordinated local
development; keep the path repository version compatible with the applications'
`shagalni/shared` constraint.

## Deployment

For deployments, resolve this package from its Git repository or from
Packagist rather than a machine-specific path.

For a direct VCS repository, replace the local `path` repository entry in the
application's `composer.json` with:

```json
{
    "type": "vcs",
    "url": "https://github.com/YoussefSayed-cs/shagalni-shared"
}
```

Keep the dependency constrained to a compatible stable release, for example:

```json
{
    "shagalni/shared": "^1.0"
}
```

Publish immutable semantic-version tags from this repository (for example,
`v1.0.0` for the first compatible release, then `v1.0.1` for a patch or
`v1.1.0` for a backward-compatible feature). Use a new major version for
breaking changes. Once a compatible tag is pushed, run
`composer update shagalni/shared` in each application and commit the resulting
lock file; deployments can then use `composer install` to install the exact
locked version.

Alternatively, publish the package to Packagist and use its package name and
the same version tags/constraint, removing the per-project VCS repository
entry when Packagist can resolve the package.
