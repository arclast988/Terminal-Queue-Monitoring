# Four foreign keys — local verification, 28 October 2026

This document records the local implementation and verification prepared for release to `arclast988/Terminal-Queue-Monitoring` on `main`.

## Implemented relationships

| Child field | Referenced field | On delete | On update |
| --- | --- | --- | --- |
| `dispatch_rounds.terminal_id` | `terminals.id` | CASCADE | CASCADE |
| `password_reset_tokens.user_id` | `users.id` | CASCADE | CASCADE |
| `routes.vehicle_type` | `vehicle_types.slug` | RESTRICT | CASCADE |
| `vehicles.type` | `vehicle_types.slug` | RESTRICT | CASCADE |

`2026-10-28-000000_EnforceLogicalReferences.php` checks all four relationships for unmatched references before changing the schema. PostgreSQL table locks and a transaction keep the checks and constraint creation atomic. It reuses equivalent constraints and suitable existing indexes, rejects conflicting definitions, and removes the stale `routes.vehicle_type` default. Inserts must supply the intended vehicle type.

Vehicle-type updates now save the parent first so PostgreSQL can cascade slug changes. Explicit child updates retain compatibility with installations that have not applied the foreign keys. Failed updates roll back the complete rename.

`start.sh` now stops before starting services when schema initialization or migrations fail. `deploy/migrate.php` checks the migration result and recorded history directly, avoiding Spark's possible zero exit status after an exception. Explicit process environment selection takes precedence over `.env`, including PHP installations using `variables_order=GPCS`.

## Local database status

The four foreign keys are installed and validated in the local `jeepneynvans.public` database. The October 28 migration is recorded. Every relationship has zero unmatched references.

A PostgreSQL backup was created before application and its archive was checked:

`build/four-fk-2026-10-28/local-before-four-fk.dump`

All 15 application tables match the backup by row count and sorted row-content digest. All 15 application sequences match. The only changed column default is `routes.vehicle_type`; existing records are preserved.

An initial rehearsal used the local configuration because `.env` shadowed its process environment selection. That exposed and prompted the environment-precedence fix. The runner also applied the previously pending `2026-10-26-000000_SimplifyDefaultDepartureRules.php`. Its unrelated changes to departure rules, queue times, sequence, and migration history were restored from the backup with concurrency checks. The four foreign keys remain installed. The original seven departure rules and 44 queue rows are preserved.

**The October 26 schedule migration remains pending locally.** Running `deploy/migrate.php` applies all pending migrations, including that older schedule change; it is not a command for applying only these four foreign keys.

## Verification

- Scoped migration through the real runner on an isolated restored schema: all 15 application tables unchanged; four foreign keys installed.
- Startup runner applied the remaining migration on the isolated schema and passed a second run with no pending migrations.
- Full PHP suite against the isolated migrated schema: **419 tests, 3,128 assertions passed**.
- Frontend unit checks against byte-verified copies of the worktree assets: **60 passed**.
- Targeted checks cover invalid references, cascade/restrict behavior, orphan preflight, rollback, repeated application, legacy rename compatibility, startup failures, and process environment precedence.
- PHP syntax, shell syntax, and whitespace checks passed.
- Independent review confirmed constraints, absence of unmatched references, preserved data, and the pending October 26 migration.

Detailed reports and test output are in `build/four-fk-2026-10-28/`. Backups and database credentials are ignored local files and must not be included in a commit or diagram archive. The worktree's test connection uses an isolated schema in `jeepneynvans_test`.

## ERD and production

The updated ERD changes all 11 repeated LR/LR¹ tags to FK/FK¹ and updates the legends. Existing table positions, geometry, styles, fields, relationship endpoints, and cardinalities are preserved. The DFD source, PNG, and SVG files are unchanged. The previous diagram folder remains available.

New exports are in `Chapter3_Four_FK_DFD_ERD_2026-10-28`, beside the previous `Chapter3_Ready_DFD_ERD_2026-10-28` folder in the user's Downloads diagram collection. The package includes editable Draw.io files, a 13-page review PDF, native SVG, and 300 dpi PNG.

Railway read access is confirmed for project `adventurous-peace`, production web service, using `arclast988/Terminal-Queue-Monitoring` on `main`. The successful deployment inspected before this release used commit `feaf3c82663b3b42b17b6d221011df9c89751dca`. These local verification results do not certify production's migration state. Verify the released commit, startup migration result, and service health after deployment.
