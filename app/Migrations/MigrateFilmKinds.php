<?php

namespace App\Migrations;

use App\Enumerations\Category;
use App\Joomla\Field;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateFilmKinds extends Migration
{
    public function table(): string
    {
        return 'nn6g0_content';
    }

    public function total(): int
    {
        return DB::connection('old')
            ->table('film_kinds_films')
            ->select(DB::raw('count(distinct film_id)'))
            ->where($this->keyName(), '>', $this->cursor->get())
            ->first()
            ->count;
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('film_kinds_films')
            ->join('film_kinds', 'film_kinds.id', '=', 'film_kinds_films.film_kind_id')
            ->groupBy('film_id')
            ->select([
                'film_id',
                DB::raw("string_agg(film_kinds.title::text, ', ') as kinds")
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
        ];
    }

    public function migrate(stdClass $row): bool
    {
        return Field::cinematic('Вид')->putValue(
            $this->joomla->migrated(Category::films, $row->film_id),
            $row->kinds
        );
    }
}
