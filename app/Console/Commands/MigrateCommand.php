<?php

namespace App\Console\Commands;

use App\Migrations\MigrateArticleAuthors;
use App\Migrations\MigrateArticleFilms;
use App\Migrations\MigrateArticlePeople;
use App\Migrations\MigrateArticles;
use App\Migrations\MigrateFilmArticles;
use App\Migrations\MigrateFilmDirectors;
use App\Migrations\MigrateFilmFormats;
use App\Migrations\MigrateFilmGenres;
use App\Migrations\MigrateFilmKinds;
use App\Migrations\MigrateFilmPeople;
use App\Migrations\MigrateFilmRoles;
use App\Migrations\MigrateFilms;
use App\Migrations\MigrateFilmStudios;
use App\Migrations\MigrateNews;
use App\Migrations\MigratePeople;
use App\Migrations\MigratePeopleProfessions;
use App\Migrations\MigratePeopleRewards;

class MigrateCommand extends \Fcz\Migrator\MigrateCommand
{
    public function __construct()
    {
        parent::__construct();

        $this->setLogger(logger());
    }

    public function migrations(): array
    {
        return [
            'News'     => fn() => new MigrateNews(),
            'Films'    => fn() => new MigrateFilms(),
            'People'   => fn() => new MigratePeople(),
            'Articles' => fn() => new MigrateArticles(),

            'Article-Films'   => fn() => new MigrateArticleFilms(),
            'Article-People'  => fn() => new MigrateArticlePeople(),
            'Article-Authors' => fn() => new MigrateArticleAuthors(),

            'People-Rewards'     => fn() => new MigratePeopleRewards(),
            'People-Professions' => fn() => new MigratePeopleProfessions(),

            'Film-Kinds'     => fn() => new MigrateFilmKinds(),
            'Film-Roles'     => fn() => new MigrateFilmRoles(),
            'Film-Genres'    => fn() => new MigrateFilmGenres(),
            'Film-People'    => fn() => new MigrateFilmPeople(),
            'Film-Formats'   => fn() => new MigrateFilmFormats(),
            'Film-Studios'   => fn() => new MigrateFilmStudios(),
            'Film-Articles'  => fn() => new MigrateFilmArticles(),
            'Film-Directors' => fn() => new MigrateFilmDirectors(),
        ];
    }
}
