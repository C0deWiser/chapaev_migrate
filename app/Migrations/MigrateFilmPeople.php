<?php

namespace App\Migrations;

use App\Enumerations\Category;
use App\Joomla\Field;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateFilmPeople extends Migration
{
    public function table(): string
    {
        return 'nn6g0_content';
    }

    public function total(): int
    {
        return DB::connection('old')
            ->table('film_people')
            ->select(DB::raw('count(distinct concat(film_id, person_role_id))'))
            ->where($this->keyName(), '>', $this->cursor->get())
            ->first()
            ->count;
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('film_people')
            ->join('person_roles', 'person_roles.id', '=', 'film_people.person_role_id')
            ->groupBy('film_id', 'person_roles.title')
            ->select([
                'film_id', 'person_roles.title as role',
                DB::raw("string_agg(film_people.person_id::text, ';') as ids"),
                DB::raw("string_agg(film_people.person_text::text, ';') as names")
            ]);
    }

    public function keyName(): string
    {
        return 'film_id';
    }

    public function dependsOn(): array
    {
        return [
            new MigrateFilms(),
            new MigratePeople()
        ];
    }

    public function migrate(stdClass $row): bool
    {
        $ids = $row->ids ? explode(';', $row->ids) : [];
        $ids = $this->joomla->metakeys(Category::faces, $ids);
        $names = $row->names ? explode(';', $row->names) : [];
        $value = array_merge($ids, $names);
        $value = array_unique($value);
        $value = array_filter($value);

        return Field::crew($row->role)->putValue(
            $this->joomla->migrated(Category::films, $row->film_id),
            implode(', ', $value),
        );
    }
}
