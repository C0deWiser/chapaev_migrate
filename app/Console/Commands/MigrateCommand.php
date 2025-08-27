<?php

namespace App\Console\Commands;

use App\Migrations\MigrateArticleFilm;
use App\Migrations\MigrateArticlePeople;
use App\Migrations\MigrateArticles;
use App\Migrations\MigrateFilmDirectors;
use App\Migrations\MigrateFilmFormats;
use App\Migrations\MigrateFilmGenres;
use App\Migrations\MigrateFilmKinds;
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

            'Article-Film'   => fn() => new MigrateArticleFilm(),
            'Article-People' => fn() => new MigrateArticlePeople(),

            'People-Rewards'     => fn() => new MigratePeopleRewards(),
            'People-Professions' => fn() => new MigratePeopleProfessions(),

            'Film-Kinds'     => fn() => new MigrateFilmKinds(),
            'Film-Genres'    => fn() => new MigrateFilmGenres(),
            'Film-Formats'   => fn() => new MigrateFilmFormats(),
            'Film-Studios'   => fn() => new MigrateFilmStudios(),
            'Film-Directors' => fn() => new MigrateFilmDirectors(),
        ];
    }
}
