<?php

class IndexController extends Pix_Controller
{
    public function init()
    {
        $this->view->user = Pix_Session::get('user_id');
    }

    public function indexAction()
    {
        $this->view->data = $_GET;
    }

    public function dataAction()
    {
        $time = intval($_GET['time']);
        $last_time = Report::search(1)->max('updated_at')->updated_at;
        $now = time();

        if ($time >= $last_time) {
            return $this->json(array(
                'status' => 0,
                'time' => $now,
                'next_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/index/data?time=' . $now,
                'data' => array(),
            ));
        }

        return $this->json(array(
            'status' => 1,
            'time' => time(),
            'next_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/index/data?time=' . $now,
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
        $this->view->is_login = $this->view->user ? true : false;
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

            try {
                $this->_checkReportData($_POST);
            } catch (Exception $e) {
                return $this->alert($e->getMessage(), '/');
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

            return $this->alert('修改成功', '/index/log/' . $report->id);
        }

    }

    public function addAction()
    {
        if ($_POST['sToken'] != Helper::getStoken()) {
            return $this->alert('sToken 錯誤', '/');
        }

        try {
            $this->_checkReportData($_POST);
        } catch (Exception $e) {
            return $this->alert($e->getMessage(), '/');
        }

        if ($report = Report::find_by_news_link($_POST['news_link'])) {
            return $this->alert('這個連結已經被人回報過了，將會把您導向該回報去', '/index/log/' . $report->id);
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

        return $this->alert('新增成功', '/index/log/' . $report->id);
    }

    protected function _checkReportData($data)
    {
        if (!$data['news_title']) {
            throw new Exception("未輸入新聞標題");
        }
        if (!$data['news_link'] or !filter_var($data['news_link'], FILTER_VALIDATE_URL)) {
            throw new Exception("請輸入合法新聞網址");
        }
        if (!$data['report_title']) {
            throw new Exception("未輸入打臉簡介");
        }
        if (!$data['report_link'] or !filter_var($data['report_link'], FILTER_VALIDATE_URL)) {
            throw new Exception("請輸入合法打臉網址");
        }
        if ($data['report_link'] == $data['news_link']) {
            throw new Exception("打臉連結不能與新聞連結相同，打臉連結請提供有提出指正該新聞錯誤證據的消息來源");
        }
    }
}
