# Student batch-account fix: TDD evidence

## Scope

This is the public CE port of the production batch-account fix. CE is treated as a fresh installation: the uniqueness rule is part of `init_db/001_initial.sql`, and no production cleanup migration or legacy-data audit is included.

## User journeys

- A researcher can import more than 20 synthetic students and receive a result for every row.
- Repeating an import does not create duplicate accounts.
- Deleting one account targets its numeric `USER_ID` only.
- A fresh CE database enforces unique, non-null student login codes from initialization.

## RED and GREEN evidence

| Guarantee | Test | RED evidence | GREEN evidence |
|---|---|---|---|
| CSV parsing preserves 25 rows and rejects malformed, duplicate, overlong, incomplete, or invalid rows | `tests/student_batch_import_test.php` | Eight intended failures because `StudentBatchImport` was absent | `Student batch import regressions: 8 passed.` |
| Creation and deletion use stable numeric identity; imported values are safe at HTML/spreadsheet sinks | `tests/account_identity_regression_test.php` | Eight intended failures against the previous code-based implementation | `Account identity regressions: 8 passed.` |
| A fresh MySQL database supports complete, repeatable, atomic imports and rejects duplicate races | `tests/student_account_integration_test.php` | The old baseline schema lacked the named unique constraint and implementation | `Student account integration regressions: 7 passed.` |

## Validation

The focused unit/regression suites and real-MySQL integration suite run in Docker. Existing PHP and JavaScript regression tests are also run by `.github/workflows/tests.yml`.

The repository has no PHP coverage instrumentation, so a numeric coverage percentage is unavailable. The changed parser, import transaction, account identity, deletion, HTML escaping, spreadsheet escaping, and database constraint paths have direct regression coverage.

## Public-edition safety

Only portable code and synthetic tests were ported. Production credentials, student data, Azure deployment configuration, duplicate-audit output, cleanup scripts, and internal rollout artifacts are excluded.
