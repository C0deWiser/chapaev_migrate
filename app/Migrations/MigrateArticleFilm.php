<?php

namespace App\Migrations;

use App\Migrations\Traits\Joomla;
use Fcz\Migrator\Migration;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateArticleFilm extends Migration
{
    use Joomla;

    public function table(): string
    {
        return 'nn6g0_content';
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('article_films')
            ->join('films', 'films.id', '=', 'article_films.film_id')
            ->join('articles', 'articles.id', '=', 'article_films.article_id')
            ->select(
                'article_films.*',
                'article_films.id as article_films.id',
                'films.title as film',
                'articles.title as article'
            );
    }

    public function keyName(): string
    {
        return 'article_films.id';
    }

    public function dependsOn(): array
    {
        return [
            new MigrateFilms(),
            new MigrateArticles(),
        ];
    }

    /**
     * @example {
     *  "id": 297
     *  "article_id": 2048
     *  "film_id": 84
     *  "created_at": "2017-03-30 20:04:06.941685"
     *  "updated_at": "2017-03-30 20:04:06.941685"
     *  "film": "Дневник директора школы"
     *  "article": "«Неочищенная» фактура"
     * }
     */
    public function migrate(stdClass $row): bool
    {
        $meta = [$row->film, $row->article];

        $this->appendMetaKeys('nn6g0_content', "film-$row->film_id", $meta);
        $this->appendMetaKeys('nn6g0_content', "article-$row->article_id", $meta);

        return true;
    }
}
