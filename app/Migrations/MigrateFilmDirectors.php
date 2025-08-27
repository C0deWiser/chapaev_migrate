<?php

namespace App\Migrations;

use App\Migrations\Traits\Joomla;
use Fcz\Migrator\Migration;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateFilmDirectors extends Migration
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
            ->table('film_directors')
            ->groupBy('film_id')
            ->select([
                'film_id',
                DB::raw("string_agg(person_id::text, ';') as people")
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
        $field = $this->registerField(
            'Режиссёр',
            'com_content.article',
            2,
            'text'
        );

        $people = array_map(
            fn($id) => $this
                ->migrated('nn6g0_contact_details', "person-$id")
                ->sole()
                ->id,
            explode(';', $row->people)
        );

        $this->putFieldValue(
            $field,
            $this->migrated('nn6g0_content', "film-$row->film_id"),
            implode(', ', $people)
        );

        return true;
    }
}
