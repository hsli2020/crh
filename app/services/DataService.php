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

        $result = $this->getStdBaseline();
        $result = $this->getActualLoad($meter, $date, $result);

        return $result;
    }

    // CRH Standard Baseline
    protected function getStdBaseline()
    {
        $sql = "SELECT hour, baseline FROM crh_baseline";
        $rows = $this->db->fetchAll($sql);

        $data = [];
        foreach ($rows as $row) {
            $hr  = sprintf("%02d:00", $row['hour']);
            $val = $row['baseline'];
            $data[$hr] = [ $hr, $val, null ];
        }

        return $data;
    }

    // CRH Actual Load
    protected function getActualLoad($meter, $date, $result)
    {
        if ($meter != 3) {
            $sql = "SELECT time AS time_utc,
                       --  CONVERT_TZ(time, 'UTC', 'America/Toronto') AS time_edt,
                           CONVERT_TZ(time, 'UTC', 'EST') AS time_est,
                           kva AS kw
                      FROM crh_meter_{$meter}
                    HAVING DATE(time_est)='$date'";
        } else {
            $sql = "SELECT m1.time AS time_utc,
                       --  CONVERT_TZ(m1.time, 'UTC', 'America/Toronto') AS time_edt,
                           CONVERT_TZ(m1.time, 'UTC', 'EST') AS time_est,
                           (m1.kva+m2.kva) AS kw
                      FROM crh_meter_1 m1
                 LEFT JOIN crh_meter_2 m2 ON m1.time=m2.time
                    HAVING DATE(time_est)='$date'";
        }
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
            $h = sprintf("%02d:00", intval($hour));
            $result[$h][2] = intval($rec['sum']/$rec['cnt']);
        }

        return $result;
    }

    public function generateBaseline($dt = '')
    {
        $date = $dt ?: date('Y-m-d');

        $b = $this->calcBaseline($date);

        $this->db->execute('TRUNCATE TABLE crh_baseline');

        foreach (range(0, 23) as $hour) {
            $this->db->insertAsDict('crh_baseline', [
                'hour'     => $hour,
               #'meter1'   => 0,
               #'meter2'   => 0,
                'baseline' => $b[$hour],
            ]);
        }

        // Save Baseline History
        $this->db->execute("DELETE FROM crh_baseline_history WHERE date='$date'");

        $this->db->insertAsDict('crh_baseline_history', [
            'date'     => $date,
            'meter1'   => '',
            'meter2'   => '',
            'baseline' => json_encode($b, JSON_FORCE_OBJECT),
        ]);
    }

    public function calcBaseline($date)
    {
        $start = date('Y-m-d', strtotime('-35 day', strtotime($date)));

        $sql = "SELECT * FROM crh_actual_load
                 WHERE `date`>='$start' AND `date`<'$date'
              ORDER BY `date` DESC";
        $data = $this->db->fetchAll($sql);

        $days = 0;
        $hourly = [];

        foreach ($data as $rec) {
            $date = $rec['date'];
            if (isWeekend($date) || isHoliday($date) || isMaintenance($date)) {
                continue;
            }

            $meter1 = json_decode($rec['meter1'], 1);
            $meter2 = json_decode($rec['meter2'], 1);

            foreach (range(0, 23) as $hour) {
                $load = $meter1[$hour] + $meter2[$hour];
                $hourly[$hour][] = $load;
            }

            if (++$days == 20) {
                break;
            }
        }

        $baseline = [];
        foreach (range(0, 23) as $hour) {
            rsort($hourly[$hour]);
            $hourly[$hour] = array_slice($hourly[$hour], 0, 15);
            $baseline[$hour] = round(array_sum($hourly[$hour]) / count($hourly[$hour]));
        }

        return $baseline;
    }

    public function generateActualLoad($dt = '')
    {
        $date = $dt ? $dt : date('Y-m-d');

        $sql = "SELECT m1.time AS time_utc,
                   --  CONVERT_TZ(m1.time, 'UTC', 'America/Toronto') AS time_edt,
                       CONVERT_TZ(m1.time, 'UTC', 'EST') AS time_est,
                       m1.kva AS meter1,
                       m2.kva AS meter2
                  FROM crh_meter_1 m1
             LEFT JOIN crh_meter_2 m2 ON m1.time=m2.time
                HAVING DATE(time_est)='$date'";
        $data = $this->db->fetchAll($sql);

        $hourly = [];
        foreach ($data as $rec) {
            $time = $rec['time_est'];
           #$dt = substr($time, 0, 10);
            $hr = intval(substr($time, 11, 2));

            if (isset($hourly[$hr])) {
                $hourly[$hr]['meter1']['sum'] += $rec['meter1'];
                $hourly[$hr]['meter1']['cnt'] += 1;

                $hourly[$hr]['meter2']['sum'] += $rec['meter2'];
                $hourly[$hr]['meter2']['cnt'] += 1;
            } else {
                $hourly[$hr]['meter1']['sum'] = $rec['meter1'];
                $hourly[$hr]['meter1']['cnt'] = 1;

                $hourly[$hr]['meter2']['sum'] = $rec['meter2'];
                $hourly[$hr]['meter2']['cnt'] = 1;
            }
        }

        $m1 = [];
        $m2 = [];
        foreach ($hourly as $hour => $rec) {
           #$h = sprintf("%02d:00", intval($hour));
            $m1[$hour] = intval($rec['meter1']['sum']/$rec['meter1']['cnt']);
            $m2[$hour] = intval($rec['meter2']['sum']/$rec['meter2']['cnt']);
        }

        // Save Actual Load
        $this->db->insertAsDict('crh_actual_load', [
            'date'   => $date,
            'meter1' => json_encode($m1, JSON_FORCE_OBJECT),
            'meter2' => json_encode($m2, JSON_FORCE_OBJECT),
        ]);
    }

    // Current 5 min Load
    public function getCurrent5MinLoad($meter)
    {
        // Meter-1
        $sql = "SELECT CONVERT_TZ(time, 'UTC', 'EST') AS time_est,
                       ROUND(kva) AS kw
                  FROM crh_meter_1
              ORDER BY time DESC LIMIT 1";
        $data = $this->db->fetchOne($sql);

        // Meter-2
        $sql = "SELECT ROUND(kva) AS kw
                  FROM crh_meter_2
              ORDER BY time DESC LIMIT 1";
        $temp = $this->db->fetchOne($sql);

        // Sum
        $data['kw'] += $temp['kw'];
        $data['time_est'] = substr($data['time_est'], 0, 16); // no seconds

        return $data;
    }

    public function get5MinLoad($meter, $date)
    {
        if ($meter != 3) {
            $sql = "SELECT CONVERT_TZ(time, 'UTC', 'EST') AS time_est,
                           ROUND(kva) AS kw
                      FROM crh_meter_{$meter}
                     WHERE CONVERT_TZ(time, 'UTC', 'EST')>'$date'";
        } else {
            $sql = "SELECT CONVERT_TZ(m1.time, 'UTC', 'EST') AS time_est,
                           ROUND(m1.kva+m2.kva) AS kw
                      FROM crh_meter_1 m1
                 LEFT JOIN crh_meter_2 m2 ON m1.time=m2.time
                     WHERE CONVERT_TZ(m1.time, 'UTC', 'EST')>'$date'";
        }
        $rows = $this->db->fetchAll($sql);

        /**
         * return [
         *    [ 'HH:MM', KW ],
         *    ...
         * ]
         */

        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                substr($row['time_est'], 11, 5), // HH:MM
                $row['kw'],
            ];
        }

        return $data;
    }

    // not-in-use
    public function get15MinLoad($meter, $date)
    {
        $meter = 1; // What to do if meter=3

        $sql = "SELECT FROM_UNIXTIME(((UNIX_TIMESTAMP(CONVERT_TZ(time, 'UTC', 'EST'))-1) DIV 900)*900 + 900) AS time_est,
                       ROUND(AVG(kva)) AS kw
                  FROM crh_meter_{$meter}
                 WHERE CONVERT_TZ(time, 'UTC', 'EST')>'$date'
              GROUP BY (UNIX_TIMESTAMP(CONVERT_TZ(time, 'UTC', 'EST'))-1) DIV 900";
        $rows = $this->db->fetchAll($sql);

        /**
         * return [
         *    [ 'HH:MM', KW ],
         *    ...
         * ]
         */

        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                substr($row['time_est'], 11, 5), // HH:MM
                $row['kw'],
            ];
        }

        return $data;
    }

    public function setDateExcluded($params)
    {
        $date = $params['date'];
        $note = $params['note'];

        try {
            $this->db->insertAsDict('date_excluded', [
                'date' => $date,
                'note' => $note,
            ]);
        } catch (\Exception $e) {
        }
    }

    public function loadExcludedDateList()
    {
        $sql = "SELECT * FROM date_excluded ORDER BY `date`";
        $rows = $this->db->fetchAll($sql);
        return $rows;
    }
}
