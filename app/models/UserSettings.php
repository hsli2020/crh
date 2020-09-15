<?php

namespace App\Models;

use Phalcon\Mvc\Model;

/**
 * App\Models\UserSettings
 */
class UserSettings extends Model
{
    public $userId;
    public $projects;
    public $dailyReport;
    public $dailyReportTemplate;
    public $monthlyReport;
    public $smartAlert;

    public function initialize()
    {
        $this->setSource('user_settings');
    }

    public function columnMap()
    {
        // Keys are the real names in the table and
        // the values their names in the application

        return array(
            'user_id'        => 'userId',
            'projects'       => 'projects',
            'daily_report'   => 'dailyReport',
            'template_dr'    => 'dailyReportTemplate',
            'monthly_report' => 'monthlyReport',
            'smart_alert'    => 'smartAlert',
        );
    }
}
