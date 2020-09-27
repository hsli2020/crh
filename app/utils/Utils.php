<?php

function toLocaltime($timeStr)
{
    $date = new \DateTime($timeStr, new \DateTimeZone('UTC'));
    $date->setTimezone(new \DateTimeZone('America/Toronto'));
    return $date->format('Y-m-d H:i:s');
}

function getNow($format, $timezone)
{
    $dt = new DateTime("now", new DateTimeZone($timezone));
    return $dt->format($format);
}
