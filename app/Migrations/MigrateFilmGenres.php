<?php

namespace App\Migrations;

use App\Enumerations\Category;
use App\Joomla\Field;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateFilmGenres extends Migration
{
    public function table(): string
    {
        return 'nn6g0_content';
    }

    public function total(): int
    {
        return DB::connection('old')
            ->table('film_genres_films')
            ->select(DB::raw('count(distinct film_id)'))
            ->where($this->keyName(), '>', $this->cursor->get())
            ->first()
            ->count;
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('film_genres_films')
            ->join('film_genres', 'film_genres.id', '=', 'film_genres_films.film_genre_id')
            ->groupBy('film_id')
            ->select([
                'film_id',
                DB::raw("string_agg(film_genres.title::text, ', ') as genres")
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
        return Field::cinematic('Жанр')->putValue(
            $this->joomla->migrated(Category::films, $row->film_id),
            $row->genres
        );
    }
}
