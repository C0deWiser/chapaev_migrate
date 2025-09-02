<?php

namespace App\Migrations;

use App\Enumerations\Category;
use App\Joomla\Field;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateArticleFilms extends Migration
{
    public function table(): string
    {
        return 'nn6g0_content';
    }

    public function total(): int
    {
        return DB::connection('old')
            ->table('article_films')
            ->select(DB::raw('count(distinct article_id)'))
            ->where($this->keyName(), '>', $this->cursor->get())
            ->first()
            ->count;
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('article_films')
            ->groupBy('article_id')
            ->select([
                'article_id',
                DB::raw("string_agg(film_id::text, ';') as related")
            ]);
    }

    public function keyName(): string
    {
        return 'article_id';
    }

    public function dependsOn(): array
    {
        return [
            new MigrateFilms(),
            new MigrateArticles(),
        ];
    }

    public function migrate(stdClass $row): bool
    {
        // Фильмы, упоминаемые в статье сохраним в филд com_content.article;3;Кино в виде json-массива

        $metakeys = $this->joomla->metakeys(Category::films, explode(';', $row->related));

        return Field::film()->putValue(
            $this->joomla->migrated(Category::articles, $row->article_id),
            $this->joomla->json_encode($metakeys)
        );
    }
}
