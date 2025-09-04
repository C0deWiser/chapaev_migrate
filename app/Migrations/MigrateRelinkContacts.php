<?php

namespace App\Migrations;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateRelinkContacts extends Migration
{
    public function table(): string
    {
        return 'nn6g0_contact_details';
    }

    public function query(): Builder
    {
        return DB::connection('new')
            ->table($this->table())
            ->select(['id', 'misc'])
            ->where('misc', 'like', '%chapaev.media%');
    }

    public function keyName(): string
    {
        return 'id';
    }

    public function dependsOn(): array
    {
        return [
            new MigrateNews(),
            new MigrateFilms(),
            new MigrateArticles(),
        ];
    }

    public function migrate(stdClass $row): bool
    {
        $data = [
            'misc' => $this->joomla->relink($row->misc)
        ];

        return DB::connection('new')
            ->table($this->table())
            ->where('id', $row->id)
            ->update($data);
    }
}
