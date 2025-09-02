<?php

namespace App\Migrations;

use App\Enumerations\Category;
use App\Joomla\Field;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateArticleAuthors extends Migration
{public function table(): string
    {
        return 'nn6g0_content';
    }

    public function total(): int
    {
        return DB::connection('old')
            ->table('article_authors')
            ->select(DB::raw('count(distinct article_id)'))
            ->where($this->keyName(), '>', $this->cursor->get())
            ->first()
            ->count;
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('article_authors')
            ->groupBy('article_id')
            ->select(
                'article_id',
                DB::raw("string_agg(person_id::text, ';') as related")
            );
    }

    public function keyName(): string
    {
        return 'article_id';
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
        // Авторов статьи сохраним в филд com_content.article;3;Автор в виде json-массива

        $metakeys = $this->joomla->metakeys(Category::authors, explode(';', $row->related));

        return Field::author()->putValue(
            $this->joomla->migrated(Category::articles, $row->article_id),
            $this->joomla->json_encode($metakeys)
        );
    }
}
