<?php


namespace App\Service;


class ApiKeyGenerator
{
    public function generateApiKey()
    {
        $longueur = '150';
        $listeCar = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

            $key = '';
            $max = mb_strlen($listeCar, '8bit') - 1;
            for ($i = 0; $i < $longueur; ++$i) {
                $key .= $listeCar[random_int(0, $max)];
            }
            return $key;
    }
}