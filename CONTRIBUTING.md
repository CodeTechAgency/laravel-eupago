# Contributing

Thanks for considering contributing to **codetech/laravel-eupago**!

## Which branch?

- **New features and improvements**: target the [`master`](https://github.com/CodeTechAgency/laravel-eupago/tree/master) branch (3.x, Laravel 10–11).
- **Security fixes for the 2.x line**: target the [`2.x`](https://github.com/CodeTechAgency/laravel-eupago/tree/2.x) branch. No other changes are accepted there.
- The [`1.x`](https://github.com/CodeTechAgency/laravel-eupago/tree/1.x) branch is end-of-life and receives no changes.

## Getting started

```bash
git clone git@github.com:CodeTechAgency/laravel-eupago.git
cd laravel-eupago
composer install
```

## Running the package locally

The repository ships a `testbench.yaml`, so the [Testbench CLI](https://packages.tools/testbench) can boot the package inside a real Laravel skeleton without creating a host application:

```bash
cp .env.example .env         # then add your Eupago sandbox credentials
vendor/bin/testbench migrate # run the package migrations (sqlite)
vendor/bin/testbench tinker  # REPL with the package booted
vendor/bin/testbench serve   # serve an app exposing the package routes
```

Inside tinker the container is up and the service provider is registered, so calls hit the real sandbox with the credentials from `.env` — e.g. `(new CodeTech\EuPago\EuPago)->status('123456789')`.

## Before submitting a pull request

Run the full quality suite locally — CI runs the same checks:

```bash
composer test      # Pest test suite
composer lint      # Pint code-style check (run `composer format` to fix)
composer analyse   # PHPStan static analysis
```

- Add tests for any change in behaviour. Tests are written with Pest — unit tests live in `tests/Unit`, feature tests in `tests/Feature`.
- Keep pull requests focused: one feature or fix per PR.
- Use a [conventional-commit](https://www.conventionalcommits.org) style title, e.g. `fix(mbway): handle missing alias`.
- Reference the related issue in the PR description. If there is no issue yet, please open one first so the change can be discussed.

## Reporting bugs

Open an issue using the bug report template and include the package, Laravel and PHP versions plus the smallest reproduction you can manage.

**Security vulnerabilities must not be reported publicly** — see the [security policy](https://github.com/CodeTechAgency/laravel-eupago/blob/master/SECURITY.md).
