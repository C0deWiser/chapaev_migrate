<?php

namespace App\Migrations\Traits;

use Illuminate\Support\Facades\DB;

trait ContactTrait
{
    use Joomla;

    protected function params(\stdClass $row): array
    {
        return [
            "show_contact_category" => "", "show_contact_list" => "", "show_tags" => "", "show_info" => "",
            "show_name"             => "", "show_position" => "", "show_email" => "", "add_mailto_link" => "",
            "show_street_address"   => "", "show_suburb" => "", "show_state" => "", "show_postcode" => "",
            "show_country"          => "", "show_telephone" => "", "show_mobile" => "", "show_fax" => "",
            "show_webpage"          => "", "show_image" => "", "show_misc" => "", "allow_vcard" => "",
            "show_articles"         => "", "articles_display_num" => "", "show_profile" => "", "contact_layout" => "",
            "show_links"            => "", "linka_name" => "", "linka" => "", "linkb_name" => "", "linkb" => "",
            "linkc_name"            => "", "linkc" => "", "linkd_name" => "", "linkd" => "", "linke_name" => "",
            "linke"                 => "", "show_email_form" => "", "show_email_copy" => "", "validate_session" => "",
            "custom_reply"          => "", "redirect" => ""
        ];
    }

}
