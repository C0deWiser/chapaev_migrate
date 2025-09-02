<?php

namespace App\Migrations;

use App\Enumerations\Category;
use App\Joomla\Field;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateFilmDirectors extends Migration
{
    public function table(): string
    {
        return 'nn6g0_content';
    }

    public function total(): int
    {
        return DB::connection('old')
            ->table('film_directors')
            ->select(DB::raw('count(distinct film_id)'))
            ->where($this->keyName(), '>', $this->cursor->get())
            ->first()
            ->count;
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('film_directors')
            ->groupBy('film_id')
            ->select([
                'film_id',
                DB::raw("string_agg(person_id::text, ';') as related")
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
            new MigratePeople(),
        ];
    }

    public function migrate(stdClass $row): bool
    {
        $metakeys = $this->joomla->metakeys(Category::faces, explode(';', $row->related));

        return Field::crew('Режиссёр')->putValue(
            $this->joomla->migrated(Category::films, $row->film_id),
            $this->joomla->json_encode($metakeys)
        );
    }
}
