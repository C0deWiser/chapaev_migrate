<?php

namespace App\Migrations;

use App\Enumerations\Category;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateNews extends Migration
{
    public function table(): string
    {
        return 'nn6g0_content';
    }

    public function query(): Builder
    {
        return DB::connection('old')->table('posts');
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
        $this->joomla->addMigrationColumn($this->table());
    }

    public function migrate(stdClass $row): bool
    {
        $migration_id = Category::news->migration_id($row->id);

        $alias = $this->joomla->makeAlias($row->title,
            unique: fn(string $alias) => DB::connection('new')
                ->table($this->table())
                ->where('alias', $alias)
                ->where('catid', Category::news)
                ->whereNot('migration', $migration_id)
                ->doesntExist()
        );

        $data = [
            'asset_id'         => 0,
            'title'            => $row->title,
            'alias'            => $alias,
            'introtext'        => $row->subtitle ?: '',
            'fulltext'         => $row->body,
            'state'            => $row->active,
            'catid'            => Category::news,
            'created'          => $row->created_at,
            'created_by'       => 0,
            'created_by_alias' => '',
            'modified'         => $row->updated_at,
            'modified_by'      => 0,
            'checked_out'      => null,
            'checked_out_time' => null,
            'publish_up'       => $row->date_from ?? $row->created_at,
            'publish_down'     => null,
            'images'           => $this->joomla->json_encode($this->joomla->images($row, [
                'image_intro' => $row->picture
                    ? 'images/'.$this->joomla->downloadAs(Category::news, $row->id, $row->picture)
                    : ''
            ])),
            'urls'             => $this->joomla->json_encode($this->joomla->urls($row)),
            'attribs'          => $this->joomla->json_encode($this->joomla->attribs($row)),
            'version'          => 1,
            'ordering'         => 0,
            'metadesc'         => '',
            'access'           => 1,
            'hits'             => 0,
            'metadata'         => $this->joomla->json_encode($this->joomla->metadata($row)),
            'featured'         => 0,
            'language'         => '*',
            'note'             => '',
        ];

        return DB::connection('new')
            ->table($this->table())
            ->updateOrInsert(['migration' => $migration_id], $data);
    }
}
