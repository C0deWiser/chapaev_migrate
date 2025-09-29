<?php

namespace App\Migrations;

use App\Enumerations\Category;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateCourseCategories extends Migration
{
    public function table(): string
    {
        return 'nn6g0_tags';
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('course_categories');
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
        $migration_id = Category::course_tag->migration_id($row->id);

        $alias = $this->joomla->makeAlias($row->title,
            unique: fn(string $alias) => DB::connection('new')
                ->table($this->table())
                ->where('alias', $alias)
                ->whereNot('migration', $migration_id)
                ->doesntExist()
        );

        $data = [
            'parent_id'        => 1,
            'path'             => $alias,
            'title'            => $row->title,
            'alias'            => $alias,
            'note'             => '',
            'description'      => '',
            'published'        => 1,
            'access'           => 1,
            'params'           => '{"tag_layout":"","tag_link_class":""}',
            'metadesc'         => '',
            'metakey'          => '',
            'metadata'         => json_encode($this->joomla->metadata($row)),
            'created_time'     => $row->created_at,
            'created_by_alias' => '',
            'modified_time'    => $row->updated_at,
            'images'           => '',
            'urls'             => '{}',
            'language'         => '*',
        ];

        return DB::connection('new')
            ->table($this->table())
            ->updateOrInsert(['migration' => $migration_id], $data);
    }
}