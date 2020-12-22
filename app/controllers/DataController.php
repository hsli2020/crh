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

    public function excludeAction()
    {
        $this->view->pageTitle = 'Excluded Dates';

        if ($this->request->isPost()) {
            $params = $this->request->getPost();
            $auth = $this->session->get('auth');
            $params['user'] = $auth['username'];
            $this->dataService->setDateExcluded($params);
            $this->response->redirect('/data/exclude');
        }

        $dates = $this->dataService->loadExcludedDateList();
        $this->view->dates = $dates;
    }
}
