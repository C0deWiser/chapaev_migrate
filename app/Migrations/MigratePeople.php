<?php

namespace App\Migrations;

use App\Enumerations\Category;
use App\Joomla\Field;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigratePeople extends Migration
{
    public function table(): string
    {
        return 'nn6g0_contact_details';
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('people');
    }

    public function keyName(): string
    {
        return 'id';
    }

    public function dependsOn(): array
    {
        return [];
    }

    public function before(): void
    {
        $this->joomla->addMigrationColumn($this->table());
    }

    public function migrate(stdClass $row): bool
    {
        $category = $row->is_author ? Category::authors : Category::faces;

        $migration_id = $category->migration_id($row->id);

        $slug = $this->joomla->makeSlug([$row->name, $row->second_name, $row->last_name],
            unique: fn(string $alias) => DB::connection('new')
                ->table($this->table())
                ->where('alias', $alias)
                ->whereNot('migration', $migration_id)
                ->doesntExist()
        );

        $data = [
            'name'             => str("$row->name $row->second_name $row->last_name")->squish()->toString(),
            'alias'            => $slug,
            'con_position'     => '',
            'address'          => '',
            'suburb'           => '',
            'state'            => '',
            'country'          => '',
            'postcode'         => '',
            'telephone'        => '',
            'fax'              => '',
            'misc'             => $row->detail_text ?? $row->preview_text ?? '',
            'image'            => $row->picture || $row->preview_picture
                ? 'images/'.$this->joomla->downloadAs($category, $row->id,
                    $row->picture ?: $row->preview_picture
                )
                : '',
            'email_to'         => '',
            'default_con'      => 0,
            'published'        => $row->active,
            'checked_out'      => null,
            'checked_out_time' => null,
            'ordering'         => $row->sort ?? 0,
            'params'           => $this->joomla->json_encode($this->joomla->params($row)),
            'user_id'          => 0,
            'catid'            => $category,
            'access'           => 1,
            'mobile'           => '',
            'webpage'          => '',
            'sortname1'        => '',
            'sortname2'        => '',
            'sortname3'        => '',
            'language'         => '*',
            'created'          => $row->created_at,
            'created_by'       => 0,
            'created_by_alias' => '',
            'modified'         => $row->updated_at,
            'modified_by'      => 0,
            'metadesc'         => '',
            'metadata'         => $this->joomla->json_encode($this->joomla->metadata($row)),
            'featured'         => 0,
            'publish_up'       => $row->created_at,
            'publish_down'     => null,
            'version'          => 1,
            'hits'             => 0,
        ];

        $response = DB::connection('new')
            ->table($this->table())
            ->updateOrInsert(['migration' => $migration_id], $data);

        $this->fields(
            $this->joomla->migrated($category, $row->id)->sole(),
            $row
        );

        return $response;
    }

    protected function fields(stdClass $new, stdClass $old): void
    {
        Field::personal('Дата рождения', type: 'calendar')->putValue($new, $old->birth_date);
        Field::personal('Дата смерти', type: 'calendar')->putValue($new, $old->death_date);
        Field::personal('Место рождения')->putValue($new, $old->birth_place);
        Field::personal('Место смерти')->putValue($new, $old->death_place);
    }
}
