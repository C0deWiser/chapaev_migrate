<?php

namespace Tests\Feature\Joomla;

use App\Joomla\Joomla;
use Tests\TestCase;

class JoomlaTest extends TestCase
{
    public function testRelink()
    {
        $text = file_get_contents(__DIR__ . '/test.html');

        $result = (new Joomla)->relink($text);

        dump($result);
    }

    public function testDownload()
    {
        $path = (new Joomla)->download('https://api.chapaev.media/uploads/ckeditor/pictures/3704/content_pamyatnik0.jpg');

        dump($path);
    }
}
