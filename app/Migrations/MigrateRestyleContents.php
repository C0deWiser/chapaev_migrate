<?php

namespace App\Migrations;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateRestyleContents extends Migration
{
    public function table(): string
    {
        return 'nn6g0_content';
    }

    public function query(): Builder
    {
        return DB::connection('new')
            ->table($this->table())
            ->select(['id', 'introtext', 'fulltext'])
            ->whereAny(['introtext', 'fulltext'], 'like', '%width:%')
            ->orWhereAny(['introtext', 'fulltext'], 'like', '%height:%');
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
            'introtext' => $this->joomla->replaceImgStyle($row->introtext),
            'fulltext'  => $this->joomla->replaceImgStyle($row->fulltext),
        ];

        return DB::connection('new')
            ->table($this->table())
            ->where('id', $row->id)
            ->update($data);
    }
}
