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

        $data = $base = $load = $marker = $band = [];
        foreach ($temp as $hour => $d) {
            if ($hour >= 8 && $hour <= 22) {
                // for table
                $tmp[0] = $d[0]; // time
                $tmp[1] = number_format($d[1]); // baseline
                $tmp[2] = number_format($d[2]); // actual load
                $tmp[3] = '-'; // variance
                if ($d[2]) {
                    $v = $d[2] - $d[1]; // variance
                    $tmp[3] = number_format($v);
                    if ($v < 0) {
                        $tmp[3] = '('. number_format(abs($v)) . ')';
                    }
                }

                $data[] = $tmp;

                // for chart
                $base[] = [ $d[0], intval($d[1]) ];
                $load[] = [ $d[0], intval($d[2]) ];

                $cmarker = $d[1] - 19000;
                $band20p = round($cmarker*0.80);

                $marker[] = [ $d[0], $cmarker ];
                $band[]   = [ $d[0], $band20p ];
            }
        }

        $temp = $this->dataService->get5MinLoad($id, $date);
        $min5load = [];
        foreach ($temp as $d) {
            $hour = substr($d[0], 0, 2);
            if ($hour >= 8 && $hour <= 22) {
                $min5load[] = [ $d[0], intval($d[1]) ];
            }
        }

        $cur5min = $this->dataService->getCurrent5MinLoad($id);
        $cur5min['kw'] = number_format($cur5min['kw']);

       #$this->view->now  = $now;
        $this->view->date = $date;
        $this->view->cur5min = $cur5min;
        $this->view->data = $data;

        $this->view->jsonBase = json_encode($base);
        $this->view->jsonLoad = json_encode($load);
        $this->view->jsonMarker = json_encode($marker);
        $this->view->jsonBand = json_encode($band);
        $this->view->jsonMin5Load = json_encode($min5load);
    }
}
