<?php

namespace App\Migrations\Traits;

use stdClass;

trait ContentTrait
{
    use Joomla;

    protected function images(stdClass $row): array
    {
        return [
            "image_intro"            => "",
            "image_intro_alt"        => "",
            "float_intro"            => "",
            "image_intro_caption"    => "",
            "image_fulltext"         => "",
            "image_fulltext_alt"     => "",
            "float_fulltext"         => "",
            "image_fulltext_caption" => ""
        ];
    }

    protected function urls(stdClass $row): array
    {
        return [
            "urla"     => "",
            "urlatext" => "",
            "targeta"  => "",
            "urlb"     => "",
            "urlbtext" => "",
            "targetb"  => "",
            "urlc"     => "",
            "urlctext" => "",
            "targetc"  => ""
        ];
    }

    protected function attribs(stdClass $row): array
    {
        return [
            "article_layout"            => "",
            "show_title"                => "",
            "link_titles"               => "",
            "show_tags"                 => "",
            "show_intro"                => "",
            "info_block_position"       => "",
            "info_block_show_title"     => "",
            "show_category"             => "",
            "link_category"             => "",
            "show_parent_category"      => "",
            "link_parent_category"      => "",
            "show_author"               => "",
            "link_author"               => "",
            "show_create_date"          => "",
            "show_modify_date"          => "",
            "show_publish_date"         => "",
            "show_item_navigation"      => "",
            "show_hits"                 => "",
            "show_noauth"               => "",
            "urls_position"             => "",
            "alternative_readmore"      => "",
            "article_page_title"        => "",
            "show_publishing_options"   => "",
            "show_article_options"      => "",
            "show_urls_images_backend"  => "",
            "show_urls_images_frontend" => ""
        ];
    }
}
