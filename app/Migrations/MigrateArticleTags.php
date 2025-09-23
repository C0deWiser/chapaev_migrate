<?php

namespace App\Migrations;

use App\Enumerations\Category;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateArticleTags extends Migration
{

    public function table(): string
    {
        return 'nn6g0_contentitem_tag_map';
    }

    public function total(): int
    {
        return DB::connection('old')
            ->table('article_categories_articles')
            ->select(DB::raw('count(distinct article_id)'))
            ->where($this->keyName(), '>', $this->cursor->get())
            ->first()
            ->count;
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('article_categories_articles')
            ->groupBy('article_id')
            ->select([
                'article_id',
                DB::raw("string_agg(article_category_id::text, ';') as related")
            ]);
    }

    public function keyName(): string
    {
        return 'article_id';
    }

    public function dependsOn(): array
    {
        return [
            new MigrateTags(),
            new MigrateArticles(),
        ];
    }

    public function before(): void
    {
        // Это наполнит таблицу nn6g0_ucm_content,
        // которая нам нужна для создания связей между статьями и тегами.

        DB::connection('new')->unprepared(
            file_get_contents(__DIR__ . '/tags_prepare.sql')
        );
    }

    /**
     * Для набивки nn6g0_contentitem_tag_map
     *
     * type_alias=com_content.article
     *
     * core_content_id — ссылка на nn6g0_ucm_content (ищем соответствие по core_type_alias=com_content.article и core_alias)
     *
     * content_item_id — ссылка на nn6g0_content
     *
     * tag_id — ссылка на nn6g0_tags
     */
    public function migrate(stdClass $row): bool
    {
        foreach (explode(';', $row->related) as $item) {
            $this->migrateTag($row, $item);
        }

        return true;
    }

    public function migrateTag(stdClass $row, $category_id): bool
    {
        try {
            $tag_id = $this->joomla->migrated_id(Category::tag, $category_id);
        } catch (RecordsNotFoundException) {
            return false;
        }

        $article = $this->joomla->migrated(Category::articles, $row->article_id)->sole();

        // Алиасы не уникальны, хотя должны.
        // Но мы знаем, что записи, которые мы мигрировали, шли позже тех, что были изначально.
        // Поэтому берём последнюю найденную.
        $core = $this->core($article->alias)->latest('core_content_id')->firstOrFail();

        return DB::connection('new')
            ->table($this->table())
            ->updateOrInsert([
                'core_content_id' => $core->core_content_id,
                'content_item_id' => $article->id,
                'tag_id'          => $tag_id,
            ], [
                'type_alias' => 'com_content.article',
                'type_id'    => 1,
            ]);
    }

    public function core(string $alias): Builder
    {
        return DB::connection('new')
            ->table('nn6g0_ucm_content')
            ->where('core_type_alias', 'com_content.article')
            ->where('core_alias', $alias);
    }
}