<?php

require "../server/common.php";

class KinopoiskTest extends PHPUnit_Framework_TestCase
{

    public function testGetRatingById(){

        $result = Kinopoisk::getRatingById(462666);

        $this->assertNotEmpty($result['kinopoisk_id']);
        $this->assertEquals($result['kinopoisk_id'], 462666);
        $this->assertNotEmpty($result['rating_kinopoisk']);
        $this->assertNotEmpty($result['rating_count_kinopoisk']);
        $this->assertNotEmpty($result['rating_imdb']);
        $this->assertNotEmpty($result['rating_count_imdb']);
    }

    /**
     * @expectedException KinopoiskException
     */
    public function testGetRatingByIdException(){

        Kinopoisk::getRatingById(0);
    }

}