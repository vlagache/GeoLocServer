<?php


namespace App\Service;


use App\Entity\Alert;

class ReverseGeocoding
{
    /**
     * @var Alert
     */
    private $alert;

    public function setAlert(Alert $alert)
    {
        $this->alert = $alert;
    }
    public function reverseGeocoding()
    {
        $lat = $this->alert->getLat();
        $lng = $this->alert->getLng();

//        $lat = 44.7715059;
//        $lng = -0.5842729;



        $config = parse_ini_file("../config.ini");
        $apiKey = $config['apiKeyGoogle'];

        $apiUrl = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$lng."&key=".$apiKey."";
        $result = json_decode(file_get_contents($apiUrl));
        $location = $result->results[0]->formatted_address;

        return $location;
    }
}