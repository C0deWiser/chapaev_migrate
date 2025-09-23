<?php

namespace App\Enumerations;

use stdClass;

enum Category: int
{
    case films = 9;
    case news = 12;
    case faces = 15;
    case articles = 16;
    case authors = 17;

    // Not a joomla real categories
    case photo = 88888;
    case gallery = 99999;
    case tag = 99998;

    public static function fromName(string $name): self
    {
        $case = self::tryFromName($name);

        if ($case) {
            return $case;
        }

        throw new \RuntimeException("No category for `$name`.");
    }

    public static function tryFromName(string $name): ?self
    {
        $cases = array_filter(self::cases(), fn(self $case) => $case->name === $name);

        return $cases ? current($cases) : null;
    }

    public function table(): string
    {
        return match ($this) {
            self::faces,
            self::authors => 'nn6g0_contact_details',
            self::photo   => 'nn6g0_phocagallery',
            self::gallery => 'nn6g0_phocagallery_categories',
            self::tag     => 'nn6g0_tags',
            default       => 'nn6g0_content',
        };
    }

    public function relation_id(int|string $new_key): string
    {
        return $this->name.'/'.$new_key;
    }

    public function migration_id(int|string $old_key): string
    {
        return match ($this) {
            self::faces,
            self::authors  => "person-$old_key",
            self::articles => "article-$old_key",
            self::films    => "film-$old_key",
            self::news     => "post-$old_key",
            self::photo    => "photo-$old_key",
            self::gallery  => "gallery-$old_key",
            self::tag      => "tag-$old_key",
        };
    }

    public function sourceDir(): string
    {
        return match ($this) {
            self::faces,
            self::authors  => 'person',
            self::articles => 'article',
            self::films    => 'film',
            self::news     => 'post',
            self::photo    => 'photo',
            self::gallery,
            self::tag      => throw new \Exception('To be implemented'),
        };
    }

    public function targetDir(): string
    {
        return match ($this) {
            self::faces    => 'faces',
            self::authors  => 'authors',
            self::articles => 'articles',
            self::films    => 'films',
            self::news     => 'news',
            self::photo    => 'phocagallery',
            self::gallery,
            self::tag      => throw new \Exception('To be implemented'),
        };
    }
}
