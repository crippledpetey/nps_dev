<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   RMA
 * @version   1.0.9
 * @build     742
 * @copyright Copyright (C) 2015 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Rma_Helper_String extends Varien_Object
{
    public function generateRandNum($length) {
        $characters = '0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public function generateRandString($length) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    /*
     * Takes a unix timestamp and returns a relative time string such as "3 minutes ago",
     *   "2 months from now", "1 year ago", etc
     * The detailLevel parameter indicates the amount of detail. The examples above are
     * with detail level 1. With detail level 2, the output might be like "3 minutes 20
     *   seconds ago", "2 years 1 month from now", etc.
     * With detail level 3, the output might be like "5 hours 3 minutes 20 seconds ago",
     *   "2 years 1 month 2 weeks from now", etc.
     */
    public function nicetime($timestamp, $detailLevel = 1) {
        $periods = array("sec", "min", "hour", "day", "week", "month", "year", "decade");
        $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

        $now = time();

        // check validity of date
        if(empty($timestamp)) {
            return "Unknown time";
        }

        // is it future date or past date
        if($now > $timestamp) {
            $difference = $now - $timestamp;
            $tense = "ago";

        } else {
            $difference = $timestamp - $now;
            $tense = "from now";
        }

        if ($difference == 0) {
            return "1 sec ago";
        }

        $remainders = array();

        for($j = 0; $j < count($lengths); $j++) {
            $remainders[$j] = floor(fmod($difference, $lengths[$j]));
            $difference = floor($difference / $lengths[$j]);
        }

        $difference = round($difference);

        $remainders[] = $difference;

        $string = "";

        for ($i = count($remainders) - 1; $i >= 0; $i--) {
            if ($remainders[$i]) {
                $string .= $remainders[$i] . " " . $periods[$i];

                if($remainders[$i] != 1) {
                    $string .= "s";
                }

                $string .= " ";

                $detailLevel--;

                if ($detailLevel <= 0) {
                    break;
                }
            }
        }

        return $string . $tense;

    }
}

