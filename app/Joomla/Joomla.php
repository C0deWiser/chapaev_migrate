<?php

namespace App\Joomla;

use App\Enumerations\Category;
use Closure;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;

class Joomla
{
    /**
     * @param  string|array  $title
     * @param  null|Closure(string):boolean  $unique  Функция проверки уникальности предложенного slug.
     *
     * @return string
     */
    public function makeSlug(string|array $title, ?Closure $unique = null): string
    {
        $title = is_array($title) ? $title : [$title];

        $title = implode(' ', $title);

        $slug = str($title)->squish()->slug()->toString();

        if (is_callable($unique)) {
            while (!$unique($slug)) {
                $slug .= '-';
            }
        }

        return $slug;
    }

    /**
     * Get a migrated record.
     */
    public function migrated(Category $category, int $old_key): Builder
    {
        return DB::connection('new')
            ->table($category->table())
            ->where('migration', $category->migration_id($old_key));
    }

    public function metadata(stdClass $row): array
    {
        return [
            "robots" => "",
            "author" => "",
            "rights" => ""
        ];
    }

    /**
     * @todo Заменить ссылки
     */
    public function relink(string $text): string
    {
        return $text;
    }

    public function addMigrationColumn(string $table, string $column = 'migration'): void
    {
        try {
            DB::connection('new')
                ->table($table)
                ->whereNull($column)
                ->first();
        } catch (QueryException $exception) {
            if (str($exception->getMessage())->contains("Unknown column '$column'")) {
                DB::connection('new')->unprepared("ALTER TABLE `$table` ADD `$column` VARCHAR(190) NULL;");
            }
        }
    }

    public function params(stdClass $row): array
    {
        return [
            "show_contact_category" => "", "show_contact_list" => "", "show_tags" => "", "show_info" => "",
            "show_name"             => "", "show_position" => "", "show_email" => "", "add_mailto_link" => "",
            "show_street_address"   => "", "show_suburb" => "", "show_state" => "", "show_postcode" => "",
            "show_country"          => "", "show_telephone" => "", "show_mobile" => "", "show_fax" => "",
            "show_webpage"          => "", "show_image" => "", "show_misc" => "", "allow_vcard" => "",
            "show_articles"         => "", "articles_display_num" => "", "show_profile" => "", "contact_layout" => "",
            "show_links"            => "", "linka_name" => "", "linka" => "", "linkb_name" => "", "linkb" => "",
            "linkc_name"            => "", "linkc" => "", "linkd_name" => "", "linkd" => "", "linke_name" => "",
            "linke"                 => "", "show_email_form" => "", "show_email_copy" => "", "validate_session" => "",
            "custom_reply"          => "", "redirect" => ""
        ];
    }

    public function images(stdClass $row): array
    {
        return [
            "image_intro"            => "",
            "image_intro_alt"        => "",
            "float_intro"            => "",
            "image_intro_caption"    => "",
            "image_fulltext"         => "",
            "image_fulltext_alt"     => "",
            "float_fulltext"         => "",
            "image_fulltext_caption" => ""
        ];
    }

    public function urls(stdClass $row): array
    {
        return [
            "urla"     => "",
            "urlatext" => "",
            "targeta"  => "",
            "urlb"     => "",
            "urlbtext" => "",
            "targetb"  => "",
            "urlc"     => "",
            "urlctext" => "",
            "targetc"  => ""
        ];
    }

    public function attribs(stdClass $row): array
    {
        return [
            "article_layout"            => "",
            "show_title"                => "",
            "link_titles"               => "",
            "show_tags"                 => "",
            "show_intro"                => "",
            "info_block_position"       => "",
            "info_block_show_title"     => "",
            "show_category"             => "",
            "link_category"             => "",
            "show_parent_category"      => "",
            "link_parent_category"      => "",
            "show_author"               => "",
            "link_author"               => "",
            "show_create_date"          => "",
            "show_modify_date"          => "",
            "show_publish_date"         => "",
            "show_item_navigation"      => "",
            "show_hits"                 => "",
            "show_noauth"               => "",
            "urls_position"             => "",
            "alternative_readmore"      => "",
            "article_page_title"        => "",
            "show_publishing_options"   => "",
            "show_article_options"      => "",
            "show_urls_images_backend"  => "",
            "show_urls_images_frontend" => ""
        ];
    }

    protected static array $keyMap = [];

    public function metakey(Category $category, int $old_key): string
    {
        if (!isset(static::$keyMap[$category->value][$old_key])) {
            static::$keyMap[$category->value][$old_key] = $category->relation_id(
                $this->migrated($category, $old_key)->sole()->id
            );
        }

        return static::$keyMap[$category->value][$old_key];
    }

    /**
     * Формирует массив кросс-ссылок на ресурсы.
     *
     * На вход передаём ключи старой базы данных.
     */
    public function metakeys(Category $category, array $old_keys): array
    {
        return array_map(fn($item) => $this->metakey($category, $item), $old_keys);
    }

    public function json_encode(mixed $value): ?string
    {
        return $value
            ? json_encode($value, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES)
            : null;
    }

    public function date_parse(?string $value): ?string
    {
        if ($value) {
            $value = explode(',', $value)[0];
            $value = explode(';', $value)[0];

            $value = str($value)
                ->replace([
                    'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября',
                    'ноября', 'декабря'
                ], [
                    'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october',
                    'november', 'december'
                ])
                // typos
                ->replace('октрября', 'october')
                ->replace('январь', 'january')
                ->replace('июнь', 'june')
                ->replace('Марта', 'march')
                ->replace([
                    'года', 'г.', '— Киев', '(Киев)', 'дата неизвестна', '— Тифлис', '— выпуск на экран в Киеве'
                ], '')
                ->trim()
                ->toString();

            return $value
                ? Carbon::parse($value)->toDateString()
                : null;
        }

        return null;
    }
}
