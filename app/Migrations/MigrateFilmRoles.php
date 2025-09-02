<?php

namespace App\Migrations;

use App\Enumerations\Category;
use App\Joomla\Field;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use stdClass;

class MigrateFilmRoles extends Migration
{
    protected string $separator = '//';

    public function table(): string
    {
        return 'nn6g0_content';
    }

    public function total(): int
    {
        return DB::connection('old')
            ->table('film_roles')
            ->select(DB::raw('count(distinct film_id)'))
            ->where($this->keyName(), '>', $this->cursor->get())
            ->first()
            ->count;
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('film_roles')
            ->groupBy('film_id')
            ->select([
                'film_id',
                DB::raw("string_agg(CASE WHEN person_id IS NOT NULL THEN person_id::text ELSE person_text::text END, '$this->separator') as people"),
                DB::raw("string_agg(person_role_text::text, '$this->separator') as roles"),
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
            new MigratePeople()
        ];
    }

    public function migrate(stdClass $row): bool
    {
        $rolesField = Field::make(
            name: 'В ролях',
            group: 0,
            type: 'subform'
        );
        $personToRoleField = Field::make(
            name: 'Человек - роль',
            group: 0
        );

        $roles = explode($this->separator, $row->roles);
        $people = explode($this->separator, $row->people);
        if (count($roles) !== count($people)) {
            throw new RuntimeException("People mismatch roles");
        }

        $value = [];

        foreach ($people as $i => $person) {
            if (is_numeric($person)) {
                $person = $this->joomla->metakey(Category::faces, $person);
            }
            $value["row$i"] = ["field{$personToRoleField->id()}" => "$person - $roles[$i]"];
        }

        return $rolesField->putValue(
            $this->joomla->migrated(Category::films, $row->film_id),
            $this->joomla->json_encode($value)
        );
    }
}
