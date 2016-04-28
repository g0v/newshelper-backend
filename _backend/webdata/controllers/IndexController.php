<?php

class IndexController extends Pix_Controller
{
    public function init()
    {
        if ($user_id = intval(Pix_Session::Get('user_id'))) {
            $this->view->user = User::find($user_id);
        }
    }

    public function indexAction()
    {
        $per_page = 20;

        $this->view->data = $_GET;
        if ($_GET['q']) {
            $q = addslashes($_GET['q']);
            $this->view->search_query = "news_title LIKE '%$q%' OR news_link LIKE '%$q%' OR report_link LIKE '%$q%' OR report_title LIKE '%$q%'";
        } else {
            $this->view->search_query = '1';
        }
        $this->view->page = max(1, intval($_GET['page']));
        $this->view->max_page = ceil(count(Report::search(1)) / $per_page);
        $this->view->per_page = $per_page;
        $this->view->pagerBaseUri = '/?page=';
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

        if ($time) {
            header('Cache-Control: max-age=86400');
        }

        return $this->json(array(
            'status' => 1,
            'time' => $now,
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
        $this->view->title = $report->news_title;
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
            $now = time();
            $report->update(array(
                'news_title' => strval($_POST['news_title']),
                'news_link' => strval($_POST['news_link']),
                'report_title' => strval($_POST['report_title']),
                'report_link' => strval($_POST['report_link']),
                'updated_at' => $now,
                'deleted_at' => intval($_POST['delete']) ? $now : 0,
            ));
            $new_values = $report->toArray();
            if ($_POST['delete']) {
                $new_values['deleted_reason'] = strval($_POST['deleted_reason']);
            }

            if ($new_values != $old_values) {
                ReportChangeLog::insert(array(
                    'report_id' => $report->id,
                    'updated_at' => $now,
                    'updated_from' => intval(ip2long($_SERVER['REMOTE_ADDR'])),
                    'updated_by' => strval($this->view->user->user_id),
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
        if ($normaled_news_link = URLNormalizer::query($_POST['news_link']) and $report = Report::find_by_news_link_unique($normaled_news_link->normalized_id)) {
            return $this->alert('這個連結已經被人回報過了，將會把您導向該回報去', '/index/log/' . $report->id);
        }

        $now = time();

        $report = Report::insert(array(
            'news_title' => strval($_POST['news_title']),
            'news_link' => strval($_POST['news_link']),
            'news_link_unique' => $normaled_news_link ? $normaled_news_link->normalized_id : '',
            'report_title' => strval($_POST['report_title']),
            'report_link' => strval($_POST['report_link']),
            'created_at' => $now,
            'updated_at' => $now,
        ));

        ReportChangeLog::insert(array(
            'report_id' => $report->id,
            'updated_at' => $now,
            'updated_from' => intval(ip2long($_SERVER['REMOTE_ADDR'])),
            'updated_by' => strval($this->view->user->user_id),
            'old_values' => '',
            'new_values' => json_encode($report->toArray()),
        ));

        return $this->alert('新增成功', '/index/log/' . $report->id);
    }

    protected function validateURL($url)
    {
        if (!preg_match('#^https?://#', $url)) {
            return false;
        }
        return true;
    }

    protected function _checkReportData($data)
    {
        if (!$data['news_title']) {
            throw new Exception("未輸入新聞標題");
        }
        if (!$data['news_link'] or !$this->validateURL($data['news_link'])) {
            throw new Exception("請輸入合法新聞網址");
        }
        if (!$data['report_title']) {
            throw new Exception("未輸入打臉簡介");
        }
        if (preg_match('#^[0-9A-Za-z]*$#', $data['news_title']) and preg_match('#^[0-9A-Za-z]*$#', $data['report_title'])) {
            throw new Exception("Invalid input");
        }

        if (!$data['report_link'] or !$this->validateURL($data['report_link'])) {
            throw new Exception("請輸入合法打臉網址");
        }
        if ($data['report_link'] == $data['news_link']) {
            throw new Exception("打臉連結不能與新聞連結相同，打臉連結請提供有提出指正該新聞錯誤證據的消息來源");
        }
        if ($normaled_report_link = URLNormalizer::query($data['report_link']) and $normaled_news_link = URLNormalizer::query($data['news_link']) and $normaled_report_link->normalized_id == $normaled_news_link->normalized_id) {
            throw new Exception("打臉連結不能與新聞連結相同，打臉連結請提供有提出指正該新聞錯誤證據的消息來源");
        }
    }

    public function healthAction()
    {
        echo 'ok';
        return $this->noview();
    }
}
