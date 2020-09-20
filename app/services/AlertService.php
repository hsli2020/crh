<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class AlertService extends Injectable
{
    protected $alerts;

    public function run()
    {
        echo "Smart Alert is running ...", EOL;

        $this->alerts = [];

        $this->checkNoData();

        if ($this->alerts) {
            $this->saveAlerts();
            $this->sendAlerts();
        }
    }

    protected function checkNoData()
    {
        $alertType = 'NO-DATA';

        $meters = [
            1 => 'crh_meter_1',
            2 => 'crh_meter_2',
        ];

        $now = time();
        foreach ($meters as $meter => $table) {
            if ($this->alertTriggered($meter, $alertType)) {
                continue;
            }

            $sql = "SELECT time FROM $table ORDER BY time DESC LIMIT 1";
            $row = $this->db->fetchOne($sql);

            $time = strtotime($row['time'].' UTC'); // UTC to LocalTime
            if ($time > 0 && $now - $time >= 30*60) {
                $this->alerts[] = [
                    'time'    => date('Y-m-d H:i:s'),
                    'meter'   => $meter,
                    'alert'   => $alertType,
                    'message' => 'No data received over 30 minutes',
                ];
            }
        }
    }

    protected function alertTriggered($meter, $alertType)
    {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM crh_alert_log WHERE meter=$meter AND alert='$alertType' AND date(time)='$today'";
        $result = $this->db->fetchOne($sql);
        return $result;
    }

    protected function generateHtml($alerts)
    {
        ob_start();
        include("./templates/alert.tpl");
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    protected function saveAlerts()
    {
        foreach ($this->alerts as $alert) {
            try {
                $this->db->insertAsDict('crh_alert_log', [
                    'time'    => $alert['time'],
                    'meter'   => $alert['meter'],
                    'alert'   => $alert['alert'],
                    'message' => $alert['message'],
                ]);
            } catch (\Exception $e) {
                echo $e->getMessage(), EOL;
            }
        }
    }

    protected function sendAlerts()
    {
        $users = [
            'lihsca@gmail.com',
        ];

        $html = $this->generateHtml($this->alerts);
        $subject = 'CRH Alert: No Data over 30 Minutes';

        foreach ($users as $email) {
            $this->sendEmail($email, $subject, $html);
        }
    }

    protected function sendEmail($recepient, $subject, $body)
    {
        $mail = new \PHPMailer();

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $today = date('Y-m-d');

#       $mail->SMTPDebug = 3;
        $mail->isSMTP();
        $mail->Host = '10.6.200.200';
        $mail->Port = 25;
        $mail->SMTPAuth = false;
        $mail->SMTPSecure = false;
        $mail->From = "OMS@greatcirclesolar.ca";
        $mail->FromName = "Great Circle Solar";
        $mail->addAddress($recepient);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = "Smart Alert can only display in HTML format";

        if (!$mail->send()) {
            $this->log("Mailer Error: " . $mail->ErrorInfo);
        } else {
            $this->log("Smart Alert sent to $recepient.");
        }
    }

    protected function log($str)
    {
        return;
        $filename = BASE_DIR . '/app/logs/alert.log';
        $str = date('Y-m-d H:i:s ') . $str . "\n";

        echo $str;
        error_log($str, 3, $filename);
    }
}
