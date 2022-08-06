<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Utility
 *
 * @author USER
 */
class Utility {

    //put your code here    

    function secondsToTime($seconds) {
        if ($seconds == null) {
            return 0;
        }
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        $dateInterval = $dtF->diff($dtT);
        $days_t = 'day';
        $hours_t = 'hr';
        $minutes_t = 'min';
        $seconds_t = 'sec';
        if ((int) $dateInterval->d > 1) {
            $days_t = 'days';
        }
        if ((int) $dateInterval->h > 1) {
            $hours_t = 'hrs';
        }
        if ((int) $dateInterval->i > 1) {
            $minutes_t = 'mins';
        }
        if ((int) $dateInterval->s > 1) {
            $seconds_t = 'secs';
        }


        if ((int) $dateInterval->d > 0) {
            if ((int) $dateInterval->d > 1 || (int) $dateInterval->h === 0) {
                return $dateInterval->format("%a $days_t");
            } else {
                return $dateInterval->format("%a $days_t, %h $hours_t");
            }
        } else if ((int) $dateInterval->h > 0) {
            if ((int) $dateInterval->h > 1 || (int) $dateInterval->i === 0) {
                return $dateInterval->format("%h $hours_t");
            } else {
                return $dateInterval->format("%h $hours_t, %i $minutes_t");
            }
        } else if ((int) $dateInterval->i > 0) {
            if ((int) $dateInterval->i > 1 || (int) $dateInterval->s === 0) {
                return $dateInterval->format("%i $minutes_t");
            } else {
                return $dateInterval->format("%i $minutes_t, %s $seconds_t");
            }
        } else {
            return $dateInterval->format("%s $seconds_t");
        }
    }

    function secondsToMinsHrs($seconds) {
        $totalHr = gmdate("H", $seconds);
        $totalMin = gmdate("i", $seconds);
        $min = ' min ';
        $hr = ' hr ';        
        if ($totalHr > 1) {
            $min = ' mins';
        }if ($totalHr > 1) {
            $hr = ' hrs ';
        }
        $totaltime = $totalHr . $hr . $totalMin . $min;         
        return $totaltime;
    }
    
    function MinsToHrsMin($minutes) {
//        $totalHr = gmdate("H", $minutes);
//        $totalMin = gmdate("i", $minutes);
        $totalHr =  date('H', mktime(0,$minutes));
        $totalMin = date('i', mktime(0,$minutes));
        $min = ' min ';
        $hr = ' hr ';        
        if ($totalHr > 1) {
            $min = ' mins';
        }if ($totalHr > 1) {
            $hr = ' hrs ';
        }
        $totaltime = $totalHr . $hr . $totalMin . $min;         
        return $totaltime;
    }

}
