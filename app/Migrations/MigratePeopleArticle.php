<?php

namespace App\Migrations;

use App\Enumerations\Category;
use App\Joomla\Field;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigratePeopleArticle extends Migration
{
    public function table(): string
    {
        return 'nn6g0_content';
    }

    public function total(): int
    {
        return DB::connection('old')
            ->table('article_people')
            ->select(DB::raw('count(distinct person_id)'))
            ->where($this->keyName(), '>', $this->cursor->get())
            ->first()
            ->count;
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('article_people')
            ->groupBy('person_id')
            ->select(
                'person_id',
                DB::raw("string_agg(article_id::text, ';') as related")
            );
    }

    public function keyName(): string
    {
        return 'person_id';
    }

    public function dependsOn(): array
    {
        return [
            new MigratePeople(),
            new MigrateArticles(),
        ];
    }

    public function migrate(stdClass $row): bool
    {
        $builder = $this->joomla->migrated(Category::faces, $row->person_id);

        $metakey = $this->joomla->merge_metakeys(
            $builder->first(),
            $this->joomla->metakeys(Category::articles, explode(';', $row->related))
        );

        return $builder->update([
            'metakey' => $this->joomla->json_encode($metakey) ?? ''
        ]);
    }
}
