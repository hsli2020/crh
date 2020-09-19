<?php

namespace App\Controllers;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        return $this->dispatcher->forward(array(
            'controller' => 'dashboard',
            'action' => 'meter'
        ));
    }

    public function testAction()
    {
        $this->view->pageTitle = 'Test Page';
        $this->view->data = __METHOD__;
        $this->flashSession->success('Everything is awesome!');
    }
}
