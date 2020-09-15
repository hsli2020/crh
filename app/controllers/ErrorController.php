<?php

namespace App\Controllers;

class ErrorController extends ControllerBase
{
    public function error401Action()
    {
        $this->response->setHeader(401, 'Unauthorized');
    }

    public function error403Action()
    {
        $this->response->setHeader(403, 'Forbidden');
    }

    public function error404Action()
    {
        $this->response->setHeader(404, 'Not Found');
    }

    public function error500Action()
    {
        $this->response->setHeader(500, 'Internal Server Error');
    }
}
