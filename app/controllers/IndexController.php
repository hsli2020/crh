<?php

namespace App\Controllers;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        // home page
    }

    public function crhAction($id = '')
    {
        if (!in_array($id, [1, 2])) {
            return $this->dispatcher->forward(array(
                'controller' => 'error',
                'action' => 'error404'
            ));
        }

        $this->view->pageTitle = "CRH $id Dashboard";

        $id = ($id + 50); // 51 -> 1, 52 -> 2

        $date = date('Y-m-d');
        $now = date('Y-m-d H:00');
        $temp = $this->dataService->getCrhData($id, $date);

        $data = $base = $load = [];
        foreach ($temp as $hour => $d) {
            if ($hour > 7 && $hour < 21) {
                $data[] = $d;
                $base[] = [ $d[0], $d[1] ];
                $load[] = [ $d[0], $d[2] ];
            }
        }

        $this->view->date = $date;
        $this->view->now  = $now;
        $this->view->data = $data;

        $this->view->jsonBase = json_encode($base);
        $this->view->jsonLoad = json_encode($load);
    }

    public function testAction()
    {
        $this->view->pageTitle = 'Test Page';
        $this->view->data = __METHOD__;
        $this->flashSession->success('Some shit happened');
    }
}
