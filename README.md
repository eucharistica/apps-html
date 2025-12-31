# EMR SIMRS (Metronic)

## Setup
1) Copy `apps/config/.env.example.php` -> `apps/config/.env.php`
2) Fill DB credentials (SIK database)
3) Create tables `emr_*` (SQL will be provided)
4) Run seed: `/apps/db/seed-admin.php`
5) Login: username `admin`, password `admin`

## Notes
- `apps/config/.env.php` is ignored by git.
