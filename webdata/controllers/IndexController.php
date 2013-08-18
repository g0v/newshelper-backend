<?php

class IndexController extends Pix_Controller
{
    public function init()
    {
        $this->view->user = Pix_Session::get('google_mail');
    }

    public function indexAction()
    {
    }

    public function dataAction()
    {
        $time = intval($_GET['time']);
        $last_time = Report::search(1)->max('updated_at')->updated_at;

        if ($time >= $last_time) {
            return $this->json(array(
                'status' => 0,
                'time' => time(),
            ));
        }

        return $this->json(array(
            'status' => 1,
            'time' => time(),
            'data' => array_map(function($r){
                $r['report_link'] = 'http://' . $_SERVER['HTTP_HOST'] . '/index/log/' . $r['id'];
                return $r;
            }, array_values(Report::search("updated_at >= {$time}")->toArray())),
        ));
    }

    public function logAction()
    {
        list(, /*index*/, /*log*/, $id) = explode('/', $this->getURI());
        if (!$report = Report::find(intval($id))) {
            return $this->alert('找不到', '/');
        }
        $this->view->report = $report;
    }

    public function editAction()
    {
        list(, /*index*/, /*edit*/, $id) = explode('/', $this->getURI());
        if (!$report = Report::find(intval($id))) {
            return $this->alert('找不到', '/');
        }
        $this->view->report = $report;

        if ($_POST) {
            if ($_POST['sToken'] != Helper::getStoken()) {
                return $this->alert('sToken 錯誤', '/');
            }

            if ($_POST['delete']) {
                if (!strval($_POST['deleted_reason'])) {
                    return $this->alert('請輸入刪除原因', '/index/edit/' . $report->id);
                }
            }

            $old_values = $report->toArray();
            $report->update(array(
                'news_title' => strval($_POST['news_title']),
                'news_link' => strval($_POST['news_link']),
                'report_title' => strval($_POST['report_title']),
                'report_link' => strval($_POST['report_link']),
                'updated_at' => time(),
                'deleted_at' => intval($_POST['delete']) ? time() : 0,
            ));
            $new_values = $report->toArray();
            if ($_POST['delete']) {
                $new_values['deleted_reason'] = strval($_POST['deleted_reason']);
            }

            if ($new_values != $old_values) {
                ReportChangeLog::insert(array(
                    'report_id' => $report->id,
                    'updated_at' => time(),
                    'updated_from' => intval(ip2long($_SERVER['REMOTE_ADDR'])),
                    'updated_by' => strval($this->view->user),
                    'old_values' => json_encode($old_values),
                    'new_values' => json_encode($new_values),
                ));
            }

            return $this->alert('新增成功', '/');
        }

        $this->redraw('index/index.phtml');
    }

    public function addAction()
    {
        if ($_POST['sToken'] != Helper::getStoken()) {
            return $this->alert('sToken 錯誤', '/');
        }

        $report = Report::insert(array(
            'news_title' => strval($_POST['news_title']),
            'news_link' => strval($_POST['news_link']),
            'report_title' => strval($_POST['report_title']),
            'report_link' => strval($_POST['report_link']),
            'created_at' => time(),
            'updated_at' => time(),
        ));

        ReportChangeLog::insert(array(
            'report_id' => $report->id,
            'updated_at' => time(),
            'updated_from' => intval(ip2long($_SERVER['REMOTE_ADDR'])),
            'updated_by' => strval($this->view->user),
            'old_values' => '',
            'new_values' => json_encode($report->toArray()),
        ));

        return $this->alert('新增成功', '/');
    }
}
