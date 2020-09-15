<?php

namespace App\Models;

use Phalcon\Mvc\Model;

/**
 * App\Models\Projects
 */
class Projects extends Model
{
    public $id;
    public $name;
    public $ftpdir;
    public $desc;
    public $active;

    public function initialize()
    {
        $this->setSource('projects');
    }
}
