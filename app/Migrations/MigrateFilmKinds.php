<?php

namespace App\Migrations;

use App\Migrations\Traits\Joomla;
use Fcz\Migrator\Migration;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateFilmKinds extends Migration
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
        $field = $this->registerField(
            'Вид',
            'com_content.article',
            1,
            'text'
        );

        $this->putFieldValue(
            $field,
            $this->migrated('nn6g0_content', "film-$row->film_id"),
            $row->kinds
        );

        return true;
    }
}
