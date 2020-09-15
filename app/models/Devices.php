<?php

namespace App\Models;

use Phalcon\Mvc\Model;

/**
 * App\Models\Devices
 */
class Devices extends Model
{
    public $id;
    public $projectId;
    public $code;
    public $type;
    public $table;
    public $model;
    public $desc;

    public function initialize()
    {
        $this->setSource('devices');
    }

    public function columnMap()
    {
        // Keys are the real names in the table and
        // the values their names in the application

        return array(
            'id'         => 'id',
            'project_id' => 'projectId',
            'devcode'    => 'code',
            'type'       => 'type',
            'table'      => 'table',
            'model'      => 'model',
            'desc'       => 'desc',
        );
    }
}
