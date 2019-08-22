<?php


namespace App\Service;



use App\Entity\Position;

class ReverseGeocoding
{

    /**
     * @var Position
     */
    private $position;

    public function setPosition(Position $position)
    {
        $this->position = $position;
    }
    public function reverseGeocoding()
    {
        $lat = $this->position->getLat();
        $lng = $this->position->getLng();

        $config = parse_ini_file("../config.ini");
        $apiKey = $config['apiKeyGoogle'];

        $apiUrl = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$lng."&key=".$apiKey."";
        $result = json_decode(file_get_contents($apiUrl));
        $location = $result->results[0]->formatted_address;

        return $location;
    }
}