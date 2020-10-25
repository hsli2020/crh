<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class ExportService extends Injectable
{
    public function export($params)
    {
        return $this->exportRawData($params);
    }

    public function exportRawData($params)
    {
       #$meter     = $params['meter'];
        $dataType  = $params['datatype'];
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
