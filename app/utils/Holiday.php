<?php

function isWeekend($date)
{
    return (date('N', strtotime($date)) >= 6);
}

function getSeason($date)
{
    $season = 'WINTER';

    list(,$month,) = explode('-', $date);
    if ($month >= 5 && $month <= 10) {
        $season = 'SUMMER';
    }

    return $season;
}

/*
function isWeekend($date)
{
    $weekDay = date('w', strtotime($date));
    return ($weekDay == 0 || $weekDay == 6);
}
*/

// Maintenance/Shutdown
function isMaintenance($date)
{
    return false;
}

function isSummer($date)
{
    return false;
}

function isWinter($date)
{
    return false;
}

function getHolidays($year)
{
    $holiday_formats = array(
        'New Years Day' => 'january 1 %d',
        'Family Day'      => 'third monday of february %d',

        'Good Friday' => function($year) {
            return date("F j, Y", easter_date($year) - 1*24*3600);
        },

        'Easter Monday' => function($year) {
            return date("F j, Y", easter_date($year) + 2*24*3600);
        },

        'Victoria Day'         => 'last monday may 25 %d',
        'Canada Day'           => 'july 1 %d',
        'August Civic Holiday' => 'first monday of august %d',
        'Labour Day'           => 'first monday of september %d',
        'Thanksgiving Day'     => 'second monday of october %d',
        'Christmas Day'        => 'december 25 %d',
        'Boxing Day'           => 'december 26 %d',
    );

    $holidays = array();
    foreach ($holiday_formats as $day => $timestring) {
        if (is_callable($timestring)) {
            $str = $timestring($year);
        } else {
            $str = sprintf($timestring, $year);
        }
        $d = strftime('%Y-%m-%d', strtotime($str));
        $holidays[$d] = $day;
    }

    return $holidays;
}

function isHoliday($date)
{
    list($y, $m, $d) = explode('-', $date);
    $holidays = getHolidays($y);
    
    return isset($holidays[$date]);
}
