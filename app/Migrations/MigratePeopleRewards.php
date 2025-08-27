<?php

namespace App\Migrations;

use App\Migrations\Traits\Joomla;
use Fcz\Migrator\Migration;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigratePeopleRewards extends Migration
{
    use Joomla;

    public function __construct()
    {
        parent::__construct();

        $this->cursor->disable();
    }

    public function table(): string
    {
        return 'nn6g0_contact_details';
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('person_rewards')
            ->join('rewards', 'rewards.id', '=', 'person_rewards.reward_id')
            ->groupBy('person_id')
            ->select([
                'person_rewards.person_id',
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
        $field = $this->registerField(
            'Награды',
            'com_contact.contact',
            0,
            'text'
        );

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

        $this->putFieldValue(
            $field,
            $this->migrated('nn6g0_contact_details', "person-$row->person_id"),
            json_encode($data, JSON_UNESCAPED_UNICODE)
        );

        return true;
    }
}
