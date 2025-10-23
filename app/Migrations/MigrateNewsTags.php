<?php

namespace App\Migrations;

use App\Enumerations\Category;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateNewsTags extends Migration
{

    public function table(): string
    {
        return 'nn6g0_contentitem_tag_map';
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
        return [
            new MigrateNewsCategories(),

            new MigrateArticles(),
            new MigrateCourses(),
            new MigrateFilms(),
            new MigrateNews(),
        ];
    }

    public function before(): void
    {
        // Это наполнит таблицу nn6g0_ucm_content,
        // которая нам нужна для создания связей между статьями и тегами.

        DB::connection('new')->unprepared(
            file_get_contents(__DIR__.'/unprepared/tags_prepare.sql')
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
     *
     * todo 1760 migrated; 11938 failed
     */
    public function migrate(stdClass $row): bool
    {
        return $this->migrateTag($row, $row->post_category_id);
    }

    public function migrateTag(stdClass $row, $category_id): bool
    {
        try {
            $tag_id = $this->joomla->migrated_id(Category::news_tag, $category_id);
        } catch (RecordsNotFoundException) {
            return false;
        }

        $post = $this->joomla->migrated(Category::news, $row->id)->sole();

        $core = $this->joomla->ucmContent($post->alias)->firstOrFail();

        return DB::connection('new')
            ->table($this->table())
            ->updateOrInsert([
                'core_content_id' => $core->core_content_id,
                'content_item_id' => $post->id,
                'tag_id'          => $tag_id,
            ], [
                'type_alias' => 'com_content.article',
                'type_id'    => 1,
            ]);
    }
}