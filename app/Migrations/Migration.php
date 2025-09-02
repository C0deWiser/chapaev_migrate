<?php

namespace App\Migrations;

use App\Joomla\Joomla;

abstract class Migration extends \Fcz\Migrator\Migration
{
    protected Joomla $joomla;

    public function __construct()
    {
        parent::__construct();

        $this->joomla = new Joomla();
    }
}
