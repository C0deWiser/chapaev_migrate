<?php

namespace App\Migrations;

use App\Enumerations\Category;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateGalleries extends Migration
{
    public function table(): string
    {
        return 'nn6g0_phocagallery_categories';
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('galleries');
    }

    public function keyName(): string
    {
        return 'id';
    }

    public function dependsOn(): array
    {
        return [
            new MigrateFilms(),
            new MigratePeople(),
        ];
    }

    public function before(): void
    {
        $this->joomla->addMigrationColumn($this->table());
    }

    /**
     * todo 632 failed
     */
    public function migrate(stdClass $row): bool
    {
        $migration_id = Category::gallery->migration_id($row->id);

        $slug = $this->joomla->makeSlug($row->title,
            unique: fn(string $alias) => DB::connection('new')
                ->table($this->table())
                ->where('alias', $alias)
                ->whereNot('migration', $migration_id)
                ->doesntExist()
        );

        $metakey = [];

        if ($row->film_id) {
            $metakey[] = $this->joomla->metakey(Category::films, $row->film_id);
        }
        if ($row->person_id) {
            $metakey[] = $this->joomla->metakey(Category::faces, $row->person_id);
        }

        $data = [
            'title' => $row->title,
            'name' => '',
            'alias' => $slug,
            'image' => '',
            'section' => '',
            'image_position' => '',
            'date' => $row->created_at ?? now(),
            'published' => 1,
            'approved' => 1,
            'ordering' => $row->sort ?? 0,
            'access' => 1,
            'latitude' => '',
            'longitude' => '',
            'geotitle' => '',
            'extid' => '',
            'exta' => '',
            'extu' => '',
            'extauth' => '',
            'imgurclient' => '',
            'imguralbum' => '',
            'extfbcatid' => '',
            'metakey' => $this->joomla->json_encode($metakey),
            'language' => '*'
        ];

        return DB::connection('new')
            ->table($this->table())
            ->updateOrInsert(['migration' => $migration_id], $data);
    }
}
