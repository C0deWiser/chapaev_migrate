<?php

namespace App\Migrations;

use App\Migrations\Traits\ContactTrait;
use Fcz\Migrator\Migration;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigratePeople extends Migration
{
    use ContactTrait;

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
        $this->addMigrationColumn();
    }

    public function migrate(stdClass $row): bool
    {
        $migration_id = "person-$row->id";

        $slug = $this->makeSlug([$row->name, $row->second_name, $row->last_name],
            fn(string $alias) => DB::connection('new')
                ->table('nn6g0_contact_details')
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
            'misc'             => $this->relink($row->detail_text ?? $row->preview_text ?? ''),
            'image'            => '',
            'email_to'         => '',
            'default_con'      => 0,
            'published'        => $row->active,
            'checked_out'      => null,
            'checked_out_time' => null,
            'ordering'         => $row->sort ?? 0,
            'params'           => json_encode($this->params($row)),
            'user_id'          => 0,
            'catid'            => $row->is_author ? self::AuthorContact : self::PeopleContact,
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
            'metadata'         => json_encode($this->metadata($row)),
            'featured'         => 0,
            'publish_up'       => $row->created_at,
            'publish_down'     => null,
            'version'          => 1,
            'hits'             => 0,
        ];

        $response = DB::connection('new')
            ->table('nn6g0_contact_details')
            ->updateOrInsert(['migration' => $migration_id], $data);

        $this->fields(
            $this->migrated('nn6g0_contact_details', $migration_id)->sole(),
            $row
        );

        return $response;
    }

    protected function fields(stdClass $new, stdClass $old): void
    {
        $birth_date = $this->registerField(
            'Дата рождения',
            'com_contact.contact',
            0,
            'calendar'
        );
        $this->putFieldValue($birth_date, $new, $old->birth_date);

        $death_date = $this->registerField(
            'Дата смерти',
            'com_contact.contact',
            0,
            'calendar'
        );
        $this->putFieldValue($death_date, $new, $old->death_date);

        $birth_place = $this->registerField(
            'Место рождения',
            'com_contact.contact',
            0,
            'text'
        );
        $this->putFieldValue($birth_place, $new, $old->birth_place);

        $death_place = $this->registerField(
            'Место смерти',
            'com_contact.contact',
            0,
            'text'
        );
        $this->putFieldValue($death_place, $new, $old->death_place);
    }
}
