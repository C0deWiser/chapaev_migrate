<?php

namespace App\Migrations;

use App\Enumerations\Category;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateFilmArticles extends Migration
{
    public function table(): string
    {
        return 'nn6g0_content';
    }

    public function total(): int
    {
        return DB::connection('old')
            ->table('article_films')
            ->select(DB::raw('count(distinct film_id)'))
            ->where($this->keyName(), '>', $this->cursor->get())
            ->first()
            ->count;
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('article_films')
            ->groupBy('film_id')
            ->select([
                'film_id',
                DB::raw("string_agg(article_id::text, ';') as related")
            ]);
    }

    public function keyName(): string
    {
        return 'film_id';
    }

    public function dependsOn(): array
    {
        return [
            new MigrateFilms(),
            new MigrateArticles(),
        ];
    }

    /**
     * В метакее фильма перечисляем все статьи про него.
     */
    public function migrate(stdClass $row): bool
    {
        $metakeys = $this->joomla->metakeys(Category::articles, explode(';', $row->related));

        return $this->joomla
            ->migrated(Category::films, $row->film_id)
            ->update([
                'metakey' => $this->joomla->json_encode($metakeys) ?? ''
            ]);
    }
}
