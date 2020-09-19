<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class DataService extends Injectable
{
    // CRH data
    public function getCrhData($prj, $date)
    {
        // return an array in the following format
        // [
        //    HOUR => [ HOUR, BASELINE, LOAD ]
        //    HOUR => [ HOUR, BASELINE, LOAD ]
        // ]

        $result = $this->getStdBaseline($prj, $date);
        $result = $this->getActualLoad($prj, $date, $result);

        return $result;
    }

    // CRH Standard Baseline
    protected function getStdBaseline($prj, $date)
    {
        $sql = "SELECT * FROM crh_baseline";
        $rows = $this->db->fetchAll($sql);

        $data = [];
        foreach ($rows as $row) {
            $hr = $row['hour']; // chart requires number, not string
            $m1 = $row['meter1'];
            $data[$hr] = [ $hr, $m1, null ];
        }

        return $data;
    }

    // CRH Actual Load
    protected function getActualLoad($prj, $date, $result)
    {
        $sql = "SELECT time AS time_utc,
                       CONVERT_TZ(time, 'UTC', 'America/Toronto') AS time_edt,
                       CONVERT_TZ(time, 'UTC', 'EST') AS time_est,
                       kva AS kw
                  FROM p{$prj}_mb_001_genmeter
                HAVING DATE(time_edt)='$date'";

        $data = $this->db->fetchAll($sql);

        $hourly = [];
        foreach ($data as $rec) {
            $time = $rec['time_edt'];
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
        $b1 = $this->calcBaseline(51); // Meter-1
        $b2 = $this->calcBaseline(52); // Meter-2

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
                       CONVERT_TZ(time, 'UTC', 'America/Toronto') AS time_edt,
                       CONVERT_TZ(time, 'UTC', 'EST') AS time_est,
                       kva AS kw
                  FROM p{$meter}_mb_001_genmeter
                HAVING time_edt>='$start' AND time_edt<'$date'
              ORDER BY time DESC";
        $data = $this->db->fetchAll($sql);

        $season = getSeason($date);

        $daily = [];
        foreach ($data as $rec) {
            $time = $rec['time_edt'];
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
