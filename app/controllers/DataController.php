<?php

namespace App\Controllers;

class DataController extends ControllerBase
{
    public function exportAction()
    {
        $this->view->pageTitle = 'Data Exporting';

        $this->view->startTime = date('Y-m-d 00:00:00', strtotime('-35 days'));
        $this->view->endTime = date('Y-m-d 00:00:00');

        if ($this->request->isPost()) {
            set_time_limit(0);
            $params = $this->request->getPost();
            $filename = $this->exportService->export($params);
            $this->startDownload($filename);
        }
    }
}
