<?php


namespace App\Service;


use App\Entity\Position;

class DistanceBetweenTwoPoints
{
    /**
     * @var Position
     */
    private $position1;

    public function setPosition1(Position $position1)
    {
        $this->position1 = $position1;
    }

    /**
     * @var Position
     */
    private $position2;
    public function setPosition2(Position $position2)
    {
        $this->position2 = $position2;
    }

    public function distanceBetweenTwoPoints()
    {
        $lat1 = $this->position1->getLat();
        $lng1 = $this->position1->getLng();
        $lat2 = $this->position2->getLat();
        $lng2 = $this->position2->getLng();

        $earth_radius = 6378137;   // Terre = sphère de 6378km de rayon
        $rlo1 = deg2rad($lng1);
        $rla1 = deg2rad($lat1);
        $rlo2 = deg2rad($lng2);
        $rla2 = deg2rad($lat2);
        $dlo = ($rlo2 - $rlo1) / 2;
        $dla = ($rla2 - $rla1) / 2;
        $a = (sin($dla) * sin($dla)) + cos($rla1) * cos($rla2) * (sin($dlo) * sin($dlo));
        $d = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $meter = ($earth_radius * $d);

        return $meter;

    }
}


//44.771487, -0.584301 => Maison
//44.772249, -0.584643 => Bout de la rue;
