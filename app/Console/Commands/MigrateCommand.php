<?php

namespace App\Console\Commands;

use App\Migrations\MigrateArticleAuthors;
use App\Migrations\MigrateArticleFilms;
use App\Migrations\MigrateArticlePeople;
use App\Migrations\MigrateArticles;
use App\Migrations\MigrateArticleTags;
use App\Migrations\MigrateCourseCategories;
use App\Migrations\MigrateCourses;
use App\Migrations\MigrateCourseTags;
use App\Migrations\MigrateFilmArticles;
use App\Migrations\MigrateFilmDirectors;
use App\Migrations\MigrateFilmFormats;
use App\Migrations\MigrateFilmGenres;
use App\Migrations\MigrateFilmKinds;
use App\Migrations\MigrateFilmPeople;
use App\Migrations\MigrateFilmRoles;
use App\Migrations\MigrateFilms;
use App\Migrations\MigrateFilmStudios;
use App\Migrations\MigrateGalleries;
use App\Migrations\MigrateNews;
use App\Migrations\MigrateNewsCategories;
use App\Migrations\MigrateNewsTags;
use App\Migrations\MigratePeople;
use App\Migrations\MigratePeopleArticle;
use App\Migrations\MigratePeopleFilm;
use App\Migrations\MigratePeopleProfessions;
use App\Migrations\MigratePeopleRewards;
use App\Migrations\MigratePhotos;
use App\Migrations\MigrateRelinkContacts;
use App\Migrations\MigrateRelinkContents;
use App\Migrations\MigrateArticleCategories;
use App\Migrations\MigrateRestyleContents;
use Illuminate\Support\Facades\DB;

class MigrateCommand extends \Fcz\Migrator\MigrateCommand
{
    public function __construct()
    {
        parent::__construct();

        $this->setLogger(logger());
    }

    public function handle(): void
    {
        parent::handle();

        DB::connection('new')->unprepared(
            file_get_contents(__DIR__.'/finally.sql')
        );
    }

    public function migrations(): array
    {
        return [
            'News'     => fn() => new MigrateNews(),
            'Films'    => fn() => new MigrateFilms(),
            'People'   => fn() => new MigratePeople(),
            'Courses'  => fn() => new MigrateCourses(),
            'Articles' => fn() => new MigrateArticles(),

            'Categories-of-News'     => fn() => new MigrateNewsCategories(),
            'Categories-of-Articles' => fn() => new MigrateArticleCategories(),
            'Categories-of-Courses'  => fn() => new MigrateCourseCategories(),

            'Article-Relink'  => fn() => new MigrateRelinkContents(),
            'Article-Restyle' => fn() => new MigrateRestyleContents(),
            'Person-Relink'   => fn() => new MigrateRelinkContacts(),

            'Course-Tags' => fn() => new MigrateCourseTags(),

            'News-Tags' => fn() => new MigrateNewsTags(),

            'Article-Tags'    => fn() => new MigrateArticleTags(),
            'Article-Films'   => fn() => new MigrateArticleFilms(),
            'Article-People'  => fn() => new MigrateArticlePeople(),
            'Article-Authors' => fn() => new MigrateArticleAuthors(),

            'Person-Film'        => fn() => new MigratePeopleFilm(),
            'Person-Article'     => fn() => new MigratePeopleArticle(),
            'Person-Rewards'     => fn() => new MigratePeopleRewards(),
            'Person-Professions' => fn() => new MigratePeopleProfessions(),

            'Film-Kinds'     => fn() => new MigrateFilmKinds(),
            'Film-Roles'     => fn() => new MigrateFilmRoles(),
            'Film-Genres'    => fn() => new MigrateFilmGenres(),
            'Film-People'    => fn() => new MigrateFilmPeople(),
            'Film-Formats'   => fn() => new MigrateFilmFormats(),
            'Film-Studios'   => fn() => new MigrateFilmStudios(),
            'Film-Articles'  => fn() => new MigrateFilmArticles(),
            'Film-Directors' => fn() => new MigrateFilmDirectors(),

            'Photos'    => fn() => new MigratePhotos(),
            'Galleries' => fn() => new MigrateGalleries(),
        ];
    }
}
