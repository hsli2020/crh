<?php

namespace App\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Validator\InclusionIn,
    Phalcon\Mvc\Model\Validator\Uniqueness;

class Users extends Model
{
    const ROLE_ADMIN = 'admin';
    const ROLE_USER  = 'user';

    public $id;
    public $username;
    public $email;
    public $password;
    public $role;
    public $active;
    public $createdon;
    public $updatedon;

    public function initialize()
    {
        $this->hasOne('id', 'App\\Models\\UserSettings', 'userId', [ 'alias' => 'settings' ]);
    }

    public function __validation()
    {
        $this->validate(new Uniqueness(
            array(
                "field"   => "username",
                "message" => "The username must be unique"
            )
        ));

        return $this->validationHasFailed() != true;
    }
}
