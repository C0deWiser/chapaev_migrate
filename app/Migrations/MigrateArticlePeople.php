<?php

namespace App\Migrations;

use App\Migrations\Traits\Joomla;
use Fcz\Migrator\Migration;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class MigrateArticlePeople extends Migration
{
    use Joomla;

    public function table(): string
    {
        return 'nn6g0_content';
    }

    public function query(): Builder
    {
        return DB::connection('old')
            ->table('article_people')
            ->join('people', 'people.id', '=', 'article_people.person_id')
            ->join('articles', 'articles.id', '=', 'article_people.article_id')
            ->select(
                'article_people.*',
                'article_people.id as article_people.id',
                'people.name as person_name',
                'people.second_name as person_second_name',
                'people.last_name as person_last_name',
                'articles.title as article'
            );
    }

    public function keyName(): string
    {
        return 'article_people.id';
    }

    public function dependsOn(): array
    {
        return [
            new MigratePeople(),
            new MigrateArticles(),
        ];
    }

    public function migrate(stdClass $row): bool
    {
        $person_name = str("$row->person_name $row->person_second_name $row->person_last_name")->squish()->toString();

        $meta = [$row->article, $person_name];

        $this->appendMetaKeys('nn6g0_content', "article-$row->article_id", $meta);
        $this->appendMetaKeys('nn6g0_contact_details', "person-$row->person_id", $meta);

        return true;
    }
}
