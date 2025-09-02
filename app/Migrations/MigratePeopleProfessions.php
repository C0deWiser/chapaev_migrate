<?php

namespace App\Migrations;

use App\Enumerations\Category;
use App\Joomla\Field;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigratePeopleProfessions extends Migration
{
    public function table(): string
    {
        return 'nn6g0_contact_details';
    }

    public function total(): int
    {
        return DB::connection('old')
            ->table('people_professions')
            ->select(DB::raw('count(distinct person_id)'))
            ->where($this->keyName(), '>', $this->cursor->get())
            ->first()
            ->count;
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('people_professions')
            ->join('professions', 'professions.id', '=', 'people_professions.profession_id')
            ->groupBy('person_id')
            ->select([
                'person_id',
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
        return Field::personal('Профессия')->putValue(
            $this->joomla->migrated(Category::faces, $row->person_id),
            str($row->professions)->lower()->toString()
        );
    }
}
