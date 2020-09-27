<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class DataService extends Injectable
{
    // CRH data
    public function getData($meter, $date)
    {
        // return an array in the following format
        // [
        //    HOUR => [ HOUR, BASELINE, LOAD ]
        //    HOUR => [ HOUR, BASELINE, LOAD ]
        // ]

        $result = $this->getStdBaseline($meter, $date);
        $result = $this->getActualLoad($meter, $date, $result);

        return $result;
    }

    // CRH Standard Baseline
    protected function getStdBaseline($meter, $date)
    {
        if ($meter == 1) $col = 'meter1'; else
        if ($meter == 2) $col = 'meter2'; else
        if ($meter == 3) $col = '(meter1 + meter2)';

        $sql = "SELECT hour, $col AS meter FROM crh_baseline";
        $rows = $this->db->fetchAll($sql);

        $data = [];
        foreach ($rows as $row) {
            $hr  = $row['hour']; // chart requires number, not string
            $val = $row['meter'];
            $data[$hr] = [ $hr, $val, null ];
        }

        return $data;
    }

    // CRH Actual Load
    protected function getActualLoad($meter, $date, $result)
    {
        if ($meter == 3) $meter = 1; // TODO: TEMP

        $sql = "SELECT time AS time_utc,
                   --  CONVERT_TZ(time, 'UTC', 'America/Toronto') AS time_edt,
                       CONVERT_TZ(time, 'UTC', 'EST') AS time_est,
                       kva AS kw
                  FROM crh_meter_{$meter}
                HAVING DATE(time_est)='$date'";

        $data = $this->db->fetchAll($sql);

        $hourly = [];
        foreach ($data as $rec) {
            $time = $rec['time_est'];
            $kwh = $rec['kw'];

            $dt = substr($time, 0, 10);
            $hr = substr($time, 11, 2);

            if (isset($hourly[$hr])) {
                $hourly[$hr]['sum'] += $kwh;
                $hourly[$hr]['cnt'] += 1;
            } else {
                $hourly[$hr]['sum'] = $kwh;
                $hourly[$hr]['cnt'] = 1;
            }
        }

        foreach ($hourly as $hour => $rec) {
            $h = intval($hour); // chart requires number, not string
            $result[$h][2] = intval($rec['sum']/$rec['cnt']);
        }

        return $result;
    }

    public function generateBaseline()
    {
        $b1 = $this->calcBaseline(1); // Meter-1
        $b2 = $this->calcBaseline(2); // Meter-2

        $this->db->execute('TRUNCATE TABLE crh_baseline');

        foreach (range(0, 23) as $hour) {
            $this->db->insertAsDict('crh_baseline', [
                'hour'   => $hour,
                'meter1' => $b1[$hour],
                'meter2' => $b2[$hour],
            ]);
        }
    }

    protected function calcBaseline($meter)
    {
        $date = date('Y-m-d');

        $start = date('Y-m-d', strtotime('-35 day'));
        $sql = "SELECT time AS time_utc,
                   --  CONVERT_TZ(time, 'UTC', 'America/Toronto') AS time_edt,
                       CONVERT_TZ(time, 'UTC', 'EST') AS time_est,
                       kva AS kw
                  FROM crh_meter_{$meter}
                HAVING time_est>='$start' AND time_est<'$date'
              ORDER BY time DESC";
        $data = $this->db->fetchAll($sql);

        $season = getSeason($date);

        $daily = [];
        foreach ($data as $rec) {
            $time = $rec['time_est'];
            $kwh = $rec['kw'];

            $dt = substr($time, 0, 10);
            $hr = substr($time, 11, 2);

            if (isWeekend($dt) || isHoliday($dt) || isMaintenance($dt)) {
                continue;
            }

            if (getSeason($dt) != $season) {
                break; // shouldn't cross seasons (SUMMER/WINTER)
            }

            if (isset($daily[$dt])) {
                $daily[$dt]['total'] += $kwh;
            } else {
                $daily[$dt]['total'] = $kwh;
            }

            if (isset($daily[$dt]['hourly'][$hr])) {
                $daily[$dt]['hourly'][$hr]['sum'] += $kwh;
                $daily[$dt]['hourly'][$hr]['cnt'] += 1;
            } else {
                $daily[$dt]['hourly'][$hr]['sum'] = $kwh;
                $daily[$dt]['hourly'][$hr]['cnt'] = 1;
            }

            if (count($daily) == 20+1) {
                array_pop($daily);
                break;
            }
        }

        uasort($daily, function($a, $b) {
            if ($a['total'] == $b['total']) { return 0; }
            return ($a['total'] < $b['total']) ? 1 : -1;
        });

        $top15 = array_slice($daily, 0, 15);

        $hourly = [];
        foreach ($top15 as $day) {
            foreach ($day['hourly'] as $hour => $rec) {
                if (isset($hourly[$hour])) {
                    $hourly[$hour]['sum'] += $rec['sum'];
                    $hourly[$hour]['cnt'] += $rec['cnt'];
                } else {
                    $hourly[$hour]['sum'] = $rec['sum'];
                    $hourly[$hour]['cnt'] = 1;
                }
            }
        }

        foreach ($hourly as $hour => $rec) {
            $avg = round($rec['sum']/$rec['cnt']);
            $hourly[$hour]['avg'] = $avg;
        }

        // Baseline (Avg)
        $result = [];
        foreach ($hourly as $hour => $rec) {
            $h = intval($hour); // chart requires number, not string
            $avg = $rec['avg'];
            $result[$h] = $avg;
        }

        ksort($result); // sort by hour
        return $result;
    }
}
