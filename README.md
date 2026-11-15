# Shagalni Shared

Shared Laravel models and enums used by the Shagalni applications.

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
