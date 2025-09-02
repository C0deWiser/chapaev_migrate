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

    public function table(): string
    {
        return match ($this) {
            self::faces,
            self::authors => 'nn6g0_contact_details',
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
        };
    }
}
