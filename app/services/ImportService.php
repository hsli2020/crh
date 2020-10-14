<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class ImportService extends Injectable
{
    public function import()
    {
        $this->log('Start importing CRH Data');

        $meters = [
            [
                'name'   => 'Meter 1',
                'ftpdir' => 'CRH1__001EC6056350',
                'table'  => 'crh_meter_1',
            ],
            [
                'name'   => 'Meter 2',
                'ftpdir' => 'CRH2_001EC60556BD',
                'table'  => 'crh_meter_2',
            ],
        ];

        $ftpRoot = "C:\\GCS-FTP-ROOT\\";

        $fileCount = 0;
        foreach ($meters as $meter) {
            echo $meter['name'], EOL;

            $dir = $ftpRoot . $meter['ftpdir'];
            foreach (glob($dir . '/*.csv') as $filename) {
                echo "\t", $filename, EOL;

                // wait until the file is completely uploaded
               #while (time() - filemtime($filename) < 10) {
               #    sleep(1);
               #}

                $fileCount++;

                $this->importFile($filename, $meter);
                $this->backupFile($filename, $dir);
            }
        }

        $this->log("Importing completed, $fileCount file(s) imported.\n");
    }

    protected function importFile($filename, $meter)
    {
        // time(UTC),error,lowalarm,highalarm,"KW_total (kW)","kwh_del (kWh)","kwh_rec (kWh)","vln_a (Volts)","vln_b (Volts)","vln_c (Volts)"
        // filename: c:\FTP-Backup\125Bermondsey_001EC6053434\mb-001.57BEE4B7_1.log.csv

        $table = $meter['table'];
        $columns = [
            'time',
            'error',
            'low_alarm',
            'high_alarm',
            'kva', // kw
            'kwh_del',
            'kwh_rec',
            'vln_a',
            'vln_b',
            'vln_c',
        ];

        if (($handle = fopen($filename, "r")) !== FALSE) {
            fgetcsv($handle); // skip first line
            while (($fields = fgetcsv($handle)) !== FALSE) {
                if (count($columns) != count($fields)) {
                    $this->log("DATA ERROR: $filename\n\t" . implode(', ', $fields));
                    continue;
                };

                $data = array_combine($columns, $fields);
                try {
                    $this->db->insertAsDict($table, $data);
                } catch (\Exception $e) {
                    echo $e->getMessage(), EOL;
                }
            }
            fclose($handle);
        }
    }

    protected function backupFile($filename, $ftpdir)
    {
        // move file to BACKUP folder, even it's not imported
        $dir = 'C:\\FTP-Backup\\' . basename($ftpdir);
        if (!file_exists($dir) && !is_dir($dir)) {
            mkdir($dir);
        }

        $newfile = $dir . '\\' . basename($filename);
        if (filesize($filename) > 0) {
            rename($filename, $newfile);
        }
    }

    protected function log($str)
    {
        $filename = BASE_DIR . '/app/logs/import.log';

        if (file_exists($filename) && filesize($filename) > 512*1024) {
            unlink($filename);
        }

        $str = date('Y-m-d H:i:s ') . $str . "\n";

        echo $str;
        error_log($str, 3, $filename);
    }
}
