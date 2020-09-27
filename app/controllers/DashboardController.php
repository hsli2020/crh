<?php

namespace App\Controllers;

class DashboardController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->pageTitle = "CRH Dashboard";

        $id = 3; // 1 + 2
        $this->loadData($id);

        $this->view->pick('/dashboard/meter');
    }

    public function meterAction($id = '')
    {
        $this->view->pageTitle = "Meter $id Dashboard";

        if (!in_array($id, [1, 2])) {
            return $this->dispatcher->forward(array(
                'controller' => 'error',
                'action' => 'error404'
            ));
        }

        $this->loadData($id);
    }

    protected function loadData($id)
    {
        $date = date('Y-m-d');
       #$now = getNow('Y-m-d H:i', 'EST');

        $temp = $this->dataService->getData($id, $date);
        $latest = $this->dataService->getLatest($id);

        $data = $base = $load = [];
        foreach ($temp as $hour => $d) {
            if ($hour >= 8 && $hour <= 22) {
                $data[] = $d;
                $base[] = [ $d[0], $d[1] ];
                $load[] = [ $d[0], $d[2] ];
            }
        }

       #$this->view->now  = $now;
        $this->view->date = $date;
        $this->view->latest = $latest;
        $this->view->data = $data;

        $this->view->jsonBase = json_encode($base);
        $this->view->jsonLoad = json_encode($load);
    }
}
