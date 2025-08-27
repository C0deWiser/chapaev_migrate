<?php

namespace App\Migrations;

use App\Migrations\Traits\Joomla;
use Fcz\Migrator\Migration;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateFilmGenres extends Migration
{
    use Joomla;

    public function __construct()
    {
        parent::__construct();

        $this->cursor->disable();
    }

    public function table(): string
    {
        return 'nn6g0_content';
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
        $field = $this->registerField(
            'Жанр',
            'com_content.article',
            1,
            'text'
        );

        $this->putFieldValue(
            $field,
            $this->migrated('nn6g0_content', "film-$row->film_id"),
            $row->genres
        );

        return true;
    }
}
