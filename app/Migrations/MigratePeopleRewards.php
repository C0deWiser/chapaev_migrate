<?php

namespace App\Migrations;

use App\Enumerations\Category;
use App\Joomla\Field;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigratePeopleRewards extends Migration
{
    public function table(): string
    {
        return 'nn6g0_contact_details';
    }

    public function total(): int
    {
        return DB::connection('old')
            ->table('person_rewards')
            ->select(DB::raw('count(distinct person_id)'))
            ->where($this->keyName(), '>', $this->cursor->get())
            ->first()
            ->count;
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('person_rewards')
            ->join('rewards', 'rewards.id', '=', 'person_rewards.reward_id')
            ->groupBy('person_id')
            ->select([
                'person_id',
                DB::raw("string_agg(person_rewards.year::text, ';') as years"),
                DB::raw("string_agg(rewards.title, ';') as rewards"),
            ]);
    }

    public function keyName(): string
    {
        return 'person_id';
    }

    public function dependsOn(): array
    {
        return [
            new MigratePeople()
        ];
    }

    public function migrate(stdClass $row): bool
    {
        $years = explode(';', $row->years);
        $rewards = explode(';', $row->rewards);

        $data = [];
        foreach ($years as $i => $year) {
            if ($rewards[$i]) {
                $data[] = [
                    'year'   => $year ?: 'undefined',
                    'reward' => $rewards[$i],
                ];
            }
        }

        return Field::personal('Награды')->putValue(
            $this->joomla->migrated(Category::faces, $row->person_id),
            $this->joomla->json_encode($data)
        );
    }
}
