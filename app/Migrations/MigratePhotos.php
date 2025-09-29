<?php

namespace App\Migrations;

use App\Enumerations\Category;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use stdClass;

class MigratePhotos extends Migration
{
    public function table(): string
    {
        return 'nn6g0_phocagallery';
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('photos')
            ->whereNotNull('picture')
            ->whereNotNull('title');
    }

    public function keyName(): string
    {
        return 'id';
    }

    public function dependsOn(): array
    {
        return [
            new MigrateGalleries(),
        ];
    }

    public function before(): void
    {
        $this->joomla->addMigrationColumn($this->table());
    }

    public function migrate(stdClass $row): bool
    {
        $migration_id = Category::photo->migration_id($row->id);

        // todo Data too long for column 'title'
        $title = str($row->title)->substr(0, 250)->toString();

        // todo они будут повторяться! Так можно?
        $alias = $this->joomla->makeAlias($title);

        $downloaded = $this->joomla->downloadAs(Category::photo, $row->id, $row->picture);

        $data = [
            'catid'       => $this->joomla->migrated_id(Category::gallery, $row->gallery_id),
            'sid'         => 0,
            'title'       => $title,
            'alias'       => $alias,
            'filename'    => $row->picture,
            'format'      => 1,
            'date'        => $row->created_at ?? now(),
            'latitude'    => '',
            'longitude'   => '',
            'geotitle'    => '',
            'imgorigsize' => Storage::size($downloaded),
            'published'   => 1,
            'approved'    => 1,
            'ordering'    => $row->sort ?? 0,
            'extid'       => '',
            'extl'        => '',
            'extm'        => '',
            'exts'        => '',
            'exto'        => '',
            'extw'        => '',
            'exth'        => '',
            'language'    => '*'
        ];

        return DB::connection('new')
            ->table($this->table())
            ->updateOrInsert(['migration' => $migration_id], $data);
    }
}
