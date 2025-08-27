<?php

namespace App\Migrations;

use App\Migrations\Traits\ContentTrait;
use Fcz\Migrator\Migration;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateFilms extends Migration
{
    use ContentTrait;

    public function table(): string
    {
        return 'nn6g0_content';
    }

    public function query(): Builder
    {
        return DB::connection('old')->table('films');
    }

    public function keyName(): string
    {
        return 'id';
    }

    public function dependsOn(): array
    {
        return [];
    }

    public function before(): void
    {
        $this->addMigrationColumn();
    }

    public function migrate(stdClass $row): bool
    {
        $migration_id = "film-$row->id";

        $slug = $this->makeSlug($row->title,
            fn(string $alias) => DB::connection('new')
                ->table('nn6g0_content')
                ->where('alias', $alias)
                ->where('catid', self::FilmContent)
                ->whereNot('migration', $migration_id)
                ->doesntExist()
        );

        $data = [
            'asset_id'         => 0,
            'title'            => $row->title,
            'alias'            => $slug,
            'introtext'        => $this->relink($row->preview_text ?? ''),
            'fulltext'         => $this->relink($row->detail_text ?? ''),
            'state'            => $row->active,
            'catid'            => self::FilmContent,
            'created'          => $row->created_at,
            'created_by'       => 0,
            'created_by_alias' => '',
            'modified'         => $row->updated_at,
            'modified_by'      => 0,
            'checked_out'      => null,
            'checked_out_time' => null,
            'publish_up'       => $row->date_from ?? $row->created_at,
            'publish_down'     => null,
            'images'           => json_encode($this->images($row)),
            'urls'             => json_encode($this->urls($row)),
            'attribs'          => json_encode($this->attribs($row)),
            'version'          => 1,
            'ordering'         => $row->sort ?? 0,
            'metadesc'         => '',
            'access'           => 1,
            'hits'             => 0,
            'metadata'         => json_encode($this->metadata($row)),
            'featured'         => 0,
            'language'         => '*',
            'note'             => '',
        ];

        return DB::connection('new')
            ->table('nn6g0_content')
            ->updateOrInsert(['migration' => $migration_id], $data);
    }
}
