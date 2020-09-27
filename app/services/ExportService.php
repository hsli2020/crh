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
        $meter     = $params['meter'];
        $dataType  = $params['datatype'];
        $startTime = $params['start-time'];
        $endTime   = $params['end-time'];

        // Filename
        $now = date('dHis');
        $basedir = str_replace('\\', '/', BASE_DIR);
        $filename = "$basedir./tmp/crh-meter-$meter-$now.csv";

        // Table name
        $table = ($meter == 2) ? 'crh_meter_2' : 'crh_meter_1';

        $sql =<<<EOS
            SELECT "Time(EST)", "KW"
            UNION ALL
            SELECT CONVERT_TZ(time, 'UTC', 'EST') AS time_est, kva AS kw
            FROM $table
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

        return $this->zipFiles($meter, $filename);
    }

    public function zipFiles($meter, $filename)
    {
        $zipFilename = BASE_DIR."/tmp/crh-meter-$meter-".date('YmdHis').'.zip';

        $zip = new \ZipArchive;
        if ($zip->open($zipFilename, \ZipArchive::CREATE) !== TRUE) {
            return false;
        }

        $zip->addFile($filename, basename($filename));
        $zip->close();

        return $zipFilename;
    }
}
