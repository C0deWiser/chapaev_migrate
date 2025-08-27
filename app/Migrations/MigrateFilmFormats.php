<?php

namespace App\Migrations;

use App\Migrations\Traits\Joomla;
use Fcz\Migrator\Migration;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateFilmFormats extends Migration
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
            ->table('film_formats_films')
            ->join('film_formats', 'film_formats.id', '=', 'film_formats_films.film_format_id')
            ->groupBy('film_id')
            ->select([
                'film_id',
                DB::raw("string_agg(film_formats.title::text, ', ') as formats")
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
            'Формат',
            'com_content.article',
            1,
            'text'
        );

        $this->putFieldValue(
            $field,
            $this->migrated('nn6g0_content', "film-$row->film_id"),
            $row->formats
        );

        return true;
    }
}
