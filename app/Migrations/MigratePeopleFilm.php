<?php

namespace App\Migrations;

use App\Enumerations\Category;
use App\Joomla\Field;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigratePeopleFilm extends Migration
{
    public function table(): string
    {
        return 'nn6g0_content';
    }

    public function total(): int
    {
        return DB::connection('old')
            ->table('film_people')
            ->select(DB::raw('count(distinct person_id)'))
            ->where($this->keyName(), '>', $this->cursor->get())
            ->first()
            ->count;
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('film_people')
            ->whereNotNull('person_id')
            ->groupBy('person_id')
            ->select([
                'person_id',
                DB::raw("string_agg(film_id::text, ';') as related")
            ]);
    }

    public function keyName(): string
    {
        return 'person_id';
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
        $builder = $this->joomla->migrated(Category::faces, $row->person_id);

        $metakey = $this->joomla->merge_metakeys(
            $builder->first(),
            $this->joomla->metakeys(Category::films, explode(';', $row->related))
        );

        return $builder->update([
            'metakey' => $this->joomla->json_encode($metakey) ?? ''
        ]);
    }
}
