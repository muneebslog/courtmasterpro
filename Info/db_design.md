# Database Design

This document outlines the database schema for the application.

## Table of Contents

- [users](#users)
- [password_reset_tokens](#password_reset_tokens)
- [sessions](#sessions)
- [cache](#cache)
- [cache_locks](#cache_locks)
- [jobs](#jobs)
- [job_batches](#job_batches)
- [failed_jobs](#failed_jobs)
- [projects](#projects)
- [project_user_roles](#project_user_roles)
- [tournaments](#tournaments)
- [events](#events)
- [players](#players)
- [teams](#teams)
- [team_players](#team_players)
- [rounds](#rounds)
- [matches](#matches)
- [sets](#sets)
- [set_players](#set_players)
- [set_scores](#set_scores)
- [match_events](#match_events)

---

### `users`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `bigint` | **Primary Key** |
| `name` | `string` | |
| `email` | `string` | **Unique** |
| `email_verified_at` | `timestamp` | Nullable |
| `password` | `string` | |
| `two_factor_secret` | `text` | Nullable |
| `two_factor_recovery_codes` | `text` | Nullable |
| `two_factor_confirmed_at` | `timestamp` | Nullable |
| `remember_token` | `string` | |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

---

### `password_reset_tokens`

| Column | Type | Constraints |
| --- | --- | --- |
| `email` | `string` | **Primary Key** |
| `token` | `string` | |
| `created_at` | `timestamp` | Nullable |

---

### `sessions`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `string` | **Primary Key** |
| `user_id` | `bigint` | **Foreign Key** to `users.id`, Nullable, Indexed |
| `ip_address` | `string(45)` | Nullable |
| `user_agent` | `text` | Nullable |
| `payload` | `longText` | |
| `last_activity` | `integer` | Indexed |

---

### `cache`

| Column | Type | Constraints |
| --- | --- | --- |
| `key` | `string` | **Primary Key** |
| `value` | `mediumText` | |
| `expiration` | `integer` | Indexed |

---

### `cache_locks`

| Column | Type | Constraints |
| --- | --- | --- |
| `key` | `string` | **Primary Key** |
| `owner` | `string` | |
| `expiration` | `integer` | Indexed |

---

### `jobs`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `bigint` | **Primary Key** |
| `queue` | `string` | Indexed |
| `payload` | `longText` | |
| `attempts` | `tinyInteger` | Unsigned |
| `reserved_at` | `integer` | Unsigned, Nullable |
| `available_at` | `integer` | Unsigned |
| `created_at` | `integer` | Unsigned |

---

### `job_batches`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `string` | **Primary Key** |
| `name` | `string` | |
| `total_jobs` | `integer` | |
| `pending_jobs` | `integer` | |
| `failed_jobs` | `integer` | |
| `failed_job_ids` | `longText` | |
| `options` | `mediumText` | Nullable |
| `cancelled_at` | `integer` | Nullable |
| `created_at` | `integer` | |
| `finished_at` | `integer` | Nullable |

---

### `failed_jobs`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `bigint` | **Primary Key** |
| `uuid` | `string` | **Unique** |
| `connection` | `text` | |
| `queue` | `text` | |
| `payload` | `longText` | |
| `exception` | `longText` | |
| `failed_at` | `timestamp` | `useCurrent()` |

---

### `projects`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `bigint` | **Primary Key** |
| `name` | `string` | |
| `description` | `text` | Nullable |
| `owner_id` | `bigint` | **Foreign Key** to `users.id` (onDelete: cascade) |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

---

### `project_user_roles`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `bigint` | **Primary Key** |
| `project_id` | `bigint` | **Foreign Key** to `projects.id` (onDelete: cascade) |
| `user_id` | `bigint` | **Foreign Key** to `users.id` (onDelete: cascade) |
| `role` | `enum` | ('admin', 'referee', 'empire', 'viewer') |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |
| `project_id`, `user_id` | | **Unique** |

---

### `tournaments`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `bigint` | **Primary Key** |
| `project_id` | `bigint` | **Foreign Key** to `projects.id` (onDelete: cascade) |
| `name` | `string` | |
| `location` | `string` | Nullable |
| `start_date` | `date` | |
| `end_date` | `date` | |
| `status` | `enum` | ('draft', 'live', 'completed'), Default: 'draft' |
| `created_by` | `bigint` | **Foreign Key** to `users.id` (onDelete: cascade) |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

---

### `events`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `bigint` | **Primary Key** |
| `tournament_id` | `bigint` | **Foreign Key** to `tournaments.id` (onDelete: cascade) |
| `name` | `string` | |
| `type` | `enum` | ('individual', 'team') |
| `default_discipline`| `enum` | ('singles', 'doubles', 'mixed') |
| `best_of_sets` | `tinyInteger`| Default: 3 |
| `status` | `enum` | ('upcoming', 'live', 'finished'), Default: 'upcoming' |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

---

### `players`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `bigint` | **Primary Key** |
| `first_name` | `string` | |
| `last_name` | `string` | Nullable |
| `gender` | `enum` | ('male', 'female', 'other') |
| `dob` | `date` | Nullable |
| `country` | `string(2)` | Nullable |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

---

### `teams`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `bigint` | **Primary Key** |
| `event_id` | `bigint` | **Foreign Key** to `events.id` (onDelete: cascade) |
| `name` | `string` | Nullable |
| `seed_number` | `integer` | Nullable |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

---

### `team_players`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `bigint` | **Primary Key** |
| `team_id` | `bigint` | **Foreign Key** to `teams.id` (onDelete: cascade) |
| `player_id` | `bigint` | **Foreign Key** to `players.id` (onDelete: cascade) |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |
| `team_id`, `player_id` | | **Unique** |

---

### `rounds`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `bigint` | **Primary Key** |
| `event_id` | `bigint` | **Foreign Key** to `events.id` (onDelete: cascade) |
| `name` | `string` | Comment: 'R32, R16, QF, SF, Final' |
| `order_no` | `integer` | |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

---

### `matches`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `bigint` | **Primary Key** |
| `round_id` | `bigint` | **Foreign Key** to `rounds.id` (onDelete: cascade) |
| `match_no` | `integer` | Unsigned, Nullable |
| `team_a_id` | `bigint` | **Foreign Key** to `teams.id` (onDelete: cascade), Nullable |
| `team_b_id` | `bigint` | **Foreign Key** to `teams.id` (onDelete: cascade), Nullable |
| `court_no` | `string` | Nullable |
| `scheduled_at` | `dateTime` | Nullable |
| `started_at` | `dateTime` | Nullable |
| `ended_at` | `dateTime` | Nullable |
| `umpire` | `string` | Nullable |
| `referee` | `string` | Nullable |
| `shuttlecock_used_count` | `integer` | Default: 1 |
| `winner_team_id` | `bigint` | **Foreign Key** to `teams.id` (onDelete: set null), Nullable |
| `status` | `enum` | ('scheduled', 'live', 'completed', 'bye'), Default: 'scheduled' |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

---

### `sets`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `bigint` | **Primary Key** |
| `match_id` | `bigint` | **Foreign Key** to `matches.id` (onDelete: cascade) |
| `set_number` | `tinyInteger` | |
| `discipline` | `enum` | ('singles', 'doubles') |
| `winner_team_id` | `bigint` | **Foreign Key** to `teams.id` (onDelete: set null), Nullable |
| `status` | `enum` | ('pending', 'live', 'completed'), Default: 'pending' |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

---

### `set_players`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `bigint` | **Primary Key** |
| `set_id` | `bigint` | **Foreign Key** to `sets.id` (onDelete: cascade) |
| `team_id` | `bigint` | **Foreign Key** to `teams.id` (onDelete: cascade) |
| `player_id` | `bigint` | **Foreign Key** to `players.id` (onDelete: cascade) |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

---

### `set_scores`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `bigint` | **Primary Key** |
| `set_id` | `bigint` | **Foreign Key** to `sets.id` (onDelete: cascade) |
| `team_id` | `bigint` | **Foreign Key** to `teams.id` (onDelete: cascade) |
| `points` | `integer` | Default: 0 |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

---

### `match_events`

| Column | Type | Constraints |
| --- | --- | --- |
| `id` | `bigint` | **Primary Key** |
| `match_id` | `bigint` | **Foreign Key** to `matches.id` (onDelete: cascade) |
| `set_id` | `bigint` | **Foreign Key** to `sets.id` (onDelete: cascade), Nullable |
| `team_id` | `bigint` | **Foreign Key** to `teams.id` (onDelete: cascade), Nullable |
| `type` | `enum` | ('timeout', 'injury', 'walkover') |
| `description` | `text` | Nullable |
| `occurred_at` | `dateTime` | |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |
