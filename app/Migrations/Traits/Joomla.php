<?php

namespace App\Migrations\Traits;

use Closure;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use stdClass;

trait Joomla
{
    const FilmContent = 9;
    const NewsContent = 12;
    const PeopleContact = 15;
    const ArticlesContent = 16;
    const AuthorContact = 17;

    protected function metadata(stdClass $row): array
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
    protected function relink(string $text): string
    {
        return $text;
    }

    protected function putFieldValue(stdClass $field, Builder|stdClass|string|int $item, $value): void
    {
        if ($item instanceof Builder) {
            $item = $item->sole();
        }

        if ($item instanceof stdClass) {
            $item = $item->id;
        }

        if ($value) {
            DB::connection('new')
                ->table('nn6g0_fields_values')
                ->updateOrInsert([
                    'item_id'  => $item,
                    'field_id' => $field->id,
                ], [
                    'value' => $value,
                ]);
        }
    }

    protected function addMigrationColumn(string $column = 'migration'): void
    {
        $table = $this->table();

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

    /**
     * @param  string|array  $title
     * @param  null|Closure(string):boolean  $unique  Функция проверки уникальности предложенного slug.
     *
     * @return string
     */
    protected function makeSlug(string|array $title, ?Closure $unique = null): string
    {
        $title = is_array($title) ? $title : [$title];

        $title = implode(' ', $title);

        $slug = str($title)->squish()->slug('-')->toString();

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
    protected function migrated(string $table, string $migration_id): Builder
    {
        return DB::connection('new')
            ->table($table)
            ->where('migration', $migration_id);
    }

    protected function appendMetaKeys(string $table, string $migration_id, string|array $keys): void
    {
        $inserted = $this->migrated($table, $migration_id)->sole();

        $currentKeys = json_decode($inserted->metakey, true) ?? [];

        $keys = is_array($keys) ? $keys : [$keys];

        $updated = array_merge($currentKeys, $keys);

        // Unduplicate
        $updated = array_unique($updated);

        // Reorder keys
        $updated = array_values($updated);

        $metakey = json_encode($updated, JSON_UNESCAPED_UNICODE);

        $this->migrated($table, $migration_id)->update(['metakey' => $metakey]);
    }

    protected static array $knownFields = [];

    protected function registerField(string $name, string $context, int $group, string $type): stdClass
    {
        $key = "$context.$group.$name";

        if (isset(self::$knownFields[$key])) {
            return self::$knownFields[$key];
        }

        $search = fn(): QueryBuilder => DB::connection('new')
            ->table('nn6g0_fields')
            ->where('group_id', $group)
            ->where('context', $context)
            ->where('title', $name);

        $field = $search()->first();

        if (!$field) {
            DB::connection('new')
                ->table('nn6g0_fields')
                ->insert([
                    'group_id'      => $group,
                    'context'       => $context,
                    'title'         => $name,
                    'asset_id'      => 0,
                    'name'          => $this->makeSlug($name),
                    'label'         => $name,
                    'type'          => $type,
                    'note'          => '',
                    'description'   => '',
                    'state'         => 1,
                    'params'        => json_encode(match ($type) {
                        'subform' => [
                            "hint"        => "", "class" => "", "label_class" => "", "show_on" => "",
                            "showon"      => "", "render_class" => "", "value_render_class" => "",
                            "showlabel"   => "1", "label_render_class" => "", "display" => "2", "prefix" => "",
                            "suffix"      => "", "layout" => "", "display_readonly" => "2", "searchindex" => "0",
                            "form_layout" => ""
                        ],
                        default   => [
                            "hint"      => "", "class" => "", "label_class" => "", "show_on" => "",
                            "showon"    => "", "render_class" => "", "value_render_class" => "",
                            "showlabel" => "1", "label_render_class" => "", "display" => "2", "prefix" => "",
                            "suffix"    => "", "layout" => "", "display_readonly" => "2", "searchindex" => "0"
                        ],
                    }),
                    'fieldparams'   => json_encode(match ($type) {
                        'text'     => ["filter" => "", "maxlength" => ""],
                        'calendar' => ["showtime" => 0],
                        'subform'  => [
                            "repeat"  => "1", "max_rows" => "",
                            "options" => ["option0" => ["customfield" => "24", "render_values" => "1"]]
                        ],
                        default    => null
                    }),
                    'language'      => '*',
                    'created_time'  => now(),
                    'modified_time' => now(),
                ]);

            $field = $search()->sole();
        }

        self::$knownFields[$key] = $field;

        return $field;
    }
}
