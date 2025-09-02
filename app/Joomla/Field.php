<?php

namespace App\Joomla;

use App\Joomla\Joomla;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use stdClass;

class Field
{
    protected static array $knownFields = [];

    public static function make(
        string $name,
        int $group,
        string $context = 'com_content.article',
        string $type = 'text'
    ): static {
        return new static($name, $context, $type, $group);
    }

    public static function personal(string $name, string $type = 'text'): static
    {
        return self::make($name, 0, context: 'com_contact.contact', type: $type);
    }

    public static function cinematic(string $name, string $type = 'text'): static
    {
        return self::make($name, 1, type: $type);
    }

    public static function crew(string $name, string $type = 'text'): static
    {
        return self::make($name, 2, type: $type);
    }

    public static function author(): static
    {
        return self::make('Автор', 3);
    }

    public static function film(): static
    {
        return self::make('Кино', 3);
    }

    public static function people(): static
    {
        return self::make('Люди', 3);
    }

    public function __construct(
        public string $name,
        public string $context,
        public string $type,
        public int $group,
    ) {
        //
    }

    /**
     * Get Joomla field PK.
     *
     * @return int
     */
    public function id(): int
    {
        if (isset(self::$knownFields[$this->name])) {
            return self::$knownFields[$this->name];
        }

        $search = fn(): QueryBuilder => DB::connection('new')
            ->table('nn6g0_fields')
            ->where('group_id', $this->group)
            ->where('context', $this->context)
            ->where('title', $this->name);

        $row = $search()->first();

        if (!$row) {
            DB::connection('new')
                ->table('nn6g0_fields')
                ->insert([
                    'group_id'      => $this->group,
                    'context'       => $this->context,
                    'title'         => $this->name,
                    'asset_id'      => 0,
                    'name'          => (new Joomla)->makeSlug($this->name),
                    'label'         => $this->name,
                    'type'          => $this->type,
                    'note'          => '',
                    'description'   => '',
                    'state'         => 1,
                    'params'        => json_encode(match ($this->type) {
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
                    'fieldparams'   => json_encode(match ($this->type) {
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

            $row = $search()->sole();

            self::$knownFields[$this->name] = $row->id;
        }

        return $row->id;
    }

    /**
     * Put field value into Joomla.
     */
    public function putValue(Builder|stdClass|string|int $query, $value): bool
    {
        if ($query instanceof Builder) {
            try {
                $query = $query->sole();
            } catch (\Throwable) {
                // skip missing record
                return false;
            }
        }

        if ($query instanceof stdClass) {
            $query = $query->id;
        }

        if ($value) {
            return DB::connection('new')
                ->table('nn6g0_fields_values')
                ->updateOrInsert(
                    ['item_id' => $query, 'field_id' => $this->id()],
                    ['value' => $value]
                );
        } else {
            DB::connection('new')
                ->table('nn6g0_fields_values')
                ->where('field_id', $this->id())
                ->where('item_id', $query)
                ->delete();

            return true;
        }
    }
}
