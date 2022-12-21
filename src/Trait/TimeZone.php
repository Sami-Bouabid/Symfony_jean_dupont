<?php
namespace App\Trait;

    trait TimeZone
    {   
        /**
         * Fonction qui permet de changer le fuseau horaire
         *
         * @param string $time_zone
         * @return void
         */
        public function setTimeZone(string $time_zone) : void
        {
            date_default_timezone_set($time_zone);
        }
    }