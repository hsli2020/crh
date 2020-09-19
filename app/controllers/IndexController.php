<?php

namespace App\Controllers;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        return $this->response->redirect('/dashboard');
    }

    public function testAction()
    {
        $this->view->pageTitle = 'Test Page';
        $this->view->data = __METHOD__;
        $this->flashSession->success('Everything is awesome!');
       #$this->view->pick('/index/index');
    }
}
