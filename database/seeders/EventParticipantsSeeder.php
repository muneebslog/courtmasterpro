<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Team;
use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class EventParticipantsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        Event::all()->each(function (Event $event) use ($faker) {

            DB::transaction(function () use ($event, $faker) {

                // Optional: clear existing teams & players for clean seed
                foreach ($event->teams as $team) {
                    $team->players()->detach();
                    $team->delete();
                }

                for ($i = 1; $i <= 32; $i++) {

                    /* ---------------------------------
                        Decide team structure
                    --------------------------------- */

                    $isIndividual = $event->type === 'individual';
                    $isSingles = $event->default_discipline === 'singles';
                    $isDoubles = $event->default_discipline === 'doubles';
                    $isTeamMixed = $event->type === 'team'
                        && $event->default_discipline === 'mixed';

                    // TEAM NAME
                    $teamName = null;

                    if ($isTeamMixed) {
                        $teamName = $faker->unique()->company;
                    }

                    $team = Team::create([
                        'event_id' => $event->id,
                        'name' => $teamName,
                    ]);

                    /* ---------------------------------
                        Decide number of players
                    --------------------------------- */

                    if ($isSingles) {
                        $playerCount = 1;
                    } elseif ($isDoubles) {
                        $playerCount = 2;
                    } else {
                        // team + mixed
                        $playerCount = rand(4, 6);
                    }

                    /* ---------------------------------
                        Create players
                    --------------------------------- */

                    for ($p = 1; $p <= $playerCount; $p++) {
                        $player = Player::create([
                            'first_name' => $faker->firstName,
                            'last_name' => $faker->lastName,
                        ]);

                        $team->players()->attach($player->id);
                    }
                }
            });

            $this->command->info(
                "Seeded 32 teams for event: {$event->name}"
            );
        });
    }
    //php artisan db:seed --class=EventParticipantsSeeder

}
