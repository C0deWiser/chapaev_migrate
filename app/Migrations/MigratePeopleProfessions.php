<?php

namespace App\Migrations;

use App\Migrations\Traits\Joomla;
use Fcz\Migrator\Migration;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigratePeopleProfessions extends Migration
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
            ->table('people_professions')
            ->join('professions', 'professions.id', '=', 'people_professions.profession_id')
            ->groupBy('person_id')
            ->select([
                'people_professions.person_id',
                DB::raw("string_agg(professions.title, ', ') as professions")
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
            'Профессия',
            'com_contact.contact',
            0,
            'text'
        );

        $this->putFieldValue(
            $field,
            $this->migrated('nn6g0_contact_details', "person-$row->person_id"),
            str($row->professions)->lower()->toString()
        );

        return true;
    }
}
