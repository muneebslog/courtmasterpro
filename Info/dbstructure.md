# Database Structure

This document outlines the database schema for the application, based on the Eloquent models.

## User
The `User` model represents the application's users.

- **Table:** `users`
- **Fillable attributes:**
    - `name`
    - `email`
    - `password`
- **Relationships:**
    - A `User` can be the `owner` of multiple `Project`s.
    - A `User` can be the `creator` of multiple `Tournament`s.
    - A `User` can have multiple `ProjectUserRole`s, linking them to projects with specific roles.

## Project
The `Project` model represents a project that can contain multiple tournaments.

- **Table:** `projects`
- **Fillable attributes:**
    - `name`
    - `description`
    - `owner_id`
- **Relationships:**
    - `owner()`: Belongs to a `User`.
    - `tournaments()`: Has many `Tournament`s.

## ProjectUserRole
This model acts as a pivot table, linking users to projects with a specific role.

- **Table:** `project_user_roles`
- **Fillable attributes:**
    - `project_id`
    - `user_id`
    - `role`
- **Relationships:**
    - `project()`: Belongs to a `Project`.
    - `user()`: Belongs to a `User`.

## Tournament
The `Tournament` model represents a single tournament within a project.

- **Table:** `tournaments`
- **Fillable attributes:**
    - `project_id`
    - `name`
    - `location`
    - `start_date`
    - `end_date`
    - `status`
    - `created_by`
- **Relationships:**
    - `project()`: Belongs to a `Project`.
    - `creator()`: Belongs to a `User`.
    - `events()`: Has many `Event`s.

## Event
An `Event` represents a specific category or competition within a tournament (e.g., Men's Singles).

- **Table:** `events`
- **Fillable attributes:**
    - `tournament_id`
    - `name`
    - `type`
    - `default_discipline`
    - `best_of_sets`
    - `status`
- **Relationships:**
    - `tournament()`: Belongs to a `Tournament`.
    - `teams()`: Has many `Team`s.
    - `rounds()`: Has many `Round`s.

## Player
The `Player` model represents an individual participant.

- **Table:** `players`
- **Fillable attributes:**
    - `first_name`
    - `last_name`
    - `gender`
    - `dob`
    - `country`
- **Relationships:**
    - A `Player` can be part of multiple `Team`s through the `TeamPlayer` pivot table.

## Team
A `Team` represents a group of players competing in an event. For singles events, a team may consist of a single player.

- **Table:** `teams`
- **Fillable attributes:**
    - `event_id`
    - `name`
    - `seed_number`
- **Relationships:**
    - `event()`: Belongs to an `Event`.
    - `players()`: Belongs to many `Player`s through the `TeamPlayer` pivot table.

## TeamPlayer
This model is a pivot table linking `Team`s and `Player`s.

- **Table:** `team_players`
- **Fillable attributes:**
    - `team_id`
    - `player_id`
- **Relationships:**
    - `team()`: Belongs to a `Team`.
    - `player()`: Belongs to a `Player`.

## Round
A `Round` represents a stage in an event's competition (e.g., Quarter-finals, Semi-finals).

- **Table:** `rounds`
- **Fillable attributes:**
    - `event_id`
    - `name`
    - `order_no`
- **Relationships:**
    - `event()`: Belongs to an `Event`.
    - `matches()`: Has many `MatchGame`s.

## MatchGame
`MatchGame` (using the `matches` table) represents a single match between two teams in a round.

- **Table:** `matches`
- **Fillable attributes:**
    - `round_id`
    - `team_a_id`
    - `team_b_id`
    - `court_no`
    - `scheduled_at`
    - `started_at`
    - `ended_at`
    - `winner_team_id`
    - `status`
- **Relationships:**
    - `round()`: Belongs to a `Round`.
    - `teamA()`: Belongs to a `Team`.
    - `teamB()`: Belongs to a `Team`.
    - `winner()`: Belongs to a `Team`.
    - `sets()`: Has many `Set`s.

## Set
A `Set` is a component of a `MatchGame` (e.g., in a best-of-3 match).

- **Table:** `sets`
- **Fillable attributes:**
    - `match_id`
    - `set_number`
    - `discipline`
    - `winner_team_id`
    - `status`
- **Relationships:**
    - `match()`: Belongs to a `MatchGame`.
    - `winner()`: Belongs to a `Team`.
    - `scores()`: Has many `SetScore`s.

## SetPlayer
This model specifies which players from a team participated in a specific `Set`. This is particularly useful for doubles or team events where the players might change between sets.

- **Table:** `set_players`
- **Fillable attributes:**
    - `set_id`
    - `team_id`
    - `player_id`
- **Relationships:**
    - `set()`: Belongs to a `Set`.
    - `team()`: Belongs to a `Team`.
    - `player()`: Belongs to a `Player`.

## SetScore
This model stores the score for each team within a `Set`.

- **Table:** `set_scores`
- **Fillable attributes:**
    - `set_id`
    - `team_id`
    - `points`
- **Relationships:**
    - `set()`: Belongs to a `Set`.
    - `team()`: Belongs to a `Team`.

## MatchEvent
This model records significant events that occur during a match, such as a score update, a fault, etc.

- **Table:** `match_events`
- **Fillable attributes:**
    - `match_id`
    - `set_id`
    - `team_id`
    - `type`
    - `description`
    - `occurred_at`
- **Relationships:**
    - `match()`: Belongs to a `MatchGame`.
    - `set()`: Belongs to a `Set`.
    - `team()`: Belongs to a `Team`.
