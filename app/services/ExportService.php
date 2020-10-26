<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class ExportService extends Injectable
{
    public function export($params)
    {
        $dataType = $params['datatype'];

        if ($dataType == 'raw-data') {
            return $this->exportRawData($params);
        }
        if ($dataType == 'baseline-actual-load') {
            return $this->exportBaselineActualLoad($params);
        }
    }

    public function exportRawData($params)
    {
       #$meter     = $params['meter'];
        $startTime = $params['start-time'];
        $endTime   = $params['end-time'];

        // Filename
        $now = date('Ymd-His');
        $basedir = str_replace('\\', '/', BASE_DIR);
        $filename = "$basedir./tmp/crh-rawdata-$now.csv";

        // Table name
       #$table = ($meter == 2) ? 'crh_meter_2' : 'crh_meter_1';

        $sql =<<<EOS
            SELECT "Time(EST)", "Meter1", "Meter2", "Sum"
            UNION ALL
            SELECT CONVERT_TZ(m1.time, 'UTC', 'EST') AS time_est, ROUND(m1.kva), ROUND(m2.kva), ROUND(m1.kva+m2.kva)
            FROM crh_meter_1 m1
            JOIN crh_meter_1 m2 on m1.time=m2.time
            HAVING time_est>='$startTime' AND time_est<='$endTime'
            INTO OUTFILE '$filename'
            FIELDS TERMINATED BY ','
            ENCLOSED BY '"'
            LINES TERMINATED BY '\n';
EOS;
        try {
            $this->db->execute($sql);
        }
        catch (\Exception $e) {
            //fpr($e->getMessage());
        }

        return $this->zipFiles($filename);
    }

    public function exportBaselineActualLoad($params)
    {
        $startTime = $params['start-time'];
        $endTime   = $params['end-time'];

        // Filename
        $now = date('Ymd-His');
        $basedir = str_replace('\\', '/', BASE_DIR);
        $filename = "$basedir./tmp/crh-baseline-$now.csv";

        $fp = fopen($filename, 'w');
        fputcsv($fp, [ "time(EST)", "Meter1", "Meter2" ]);

        $sql = "SELECT * FROM crh_baseline_history WHERE date>='$startTime' AND date<='$endTime'";
        $rows = $this->db->fetchAll($sql);
        foreach ($rows as $row) {
            $date = $row['date'];
            $meter1 = json_decode($row['meter1'], 1);
            $meter2 = json_decode($row['meter2'], 1);
            foreach ($meter1 as $hour => $m1kw) {
                if ($hour >= 8 && $hour <= 22) {
                    $m2kw = $meter2[$hour];
                    fputcsv($fp, [ "$date $hour:00", $m1kw, $m2kw ]);
                }
            }
        }

        fclose($fp);

        return $this->zipFiles($filename);
    }

    public function zipFiles($filename)
    {
        $zipFilename = substr($filename, 0, -4).'.zip';

        $zip = new \ZipArchive;
        if ($zip->open($zipFilename, \ZipArchive::CREATE) !== TRUE) {
            return false;
        }

        $zip->addFile($filename, basename($filename));
        $zip->close();

        return $zipFilename;
    }
}
