<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class StatisticsController extends \Controller\BaseStalkerController {

    protected $allVideoStat = array();

    protected $allAbonentStat = array();

    protected $allNoActiveAbonentStat = array();

    protected $taskAllState = array();

    protected $taskType = array();
    
    private $videoQuality = array(
        0=>array('id' => '1', 'title' => 'SD'), 
        1=>array('id' => '2', 'title' => 'HD'), 
    );
    
    private $stateColor = array('primary','success','warning','danger', 'default');
    
    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);

        $this->allVideoStat = array(
            array('id' => 'all',    'title' => $this->setLocalization('General')),
            array('id' => 'daily',  'title' => $this->setLocalization('By days')),
            array('id' => 'genre',  'title' => $this->setLocalization('By genres'))
        );

        $this->allAbonentStat = array(
            array('id' => 'tv',     'title' => $this->setLocalization('TV')),
            array('id' => 'video',  'title' => $this->setLocalization('Movies')),
            array('id' => 'anec',   'title' => $this->setLocalization('Humor'))
        );

        $this->allNoActiveAbonentStat = array(
            array('id' => 'tv',     'title' => $this->setLocalization('TV')),
            array('id' => 'video',  'title' => $this->setLocalization('Movies'))
        );

        $this->taskAllState = array(
            0=>array('id' => '1', 'title' => $this->setLocalization('Open')),
            1=>array('id' => '2', 'title' => $this->setLocalization('Done')),
            2=>array('id' => '3', 'title' => $this->setLocalization('Rejected')),
            3=>array('id' => '4', 'title' => $this->setLocalization('Expired')),
            4=>array('id' => '5', 'title' => $this->setLocalization('Archive'))
        );

        $this->taskType = array(
            array('id' => 'moderator_tasks',    'title' => $this->setLocalization('Movies')),
            array('id' => 'karaoke',            'title' => $this->setLocalization('Karaoke'))
        );
    }

    // ------------------- action method ---------------------------------------

    public function index() {

        if (empty($this->app['action_alias'])) {
            return $this->app->redirect($this->app['controller_alias'] . '/stat-video');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function stat_video() {

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if (empty($this->data['filters']['stat_to'])) {
            if (empty($this->data['filters'])) {
                $this->data['filters'] = array('stat_to' => 'all');
            } else {
                $this->data['filters']['stat_to'] = 'all';
            }
            $dropdown_filters = '';
        } else {
            $dropdown_filters = "-filters-{$this->data['filters']['stat_to']}";
        }

        $this->app['filters'] = $this->data['filters'];
        $filter = $this->app['filters']['stat_to'];

        $this->app['allVideoStat'] = $this->allVideoStat;

        $attr_func = "getVideo" . ucfirst($filter) . "DropdownAttribute";

        $attribute = $this->$attr_func();
        $this->checkDropdownAttribute($attribute, $dropdown_filters);
        $this->app['dropdownAttribute'] = $attribute;
        
        $list = $this->stat_video_list_json();
        
        $this->app['allStat'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        $date_fields = $this->getBeginEndPeriod();
        $this->app['minDatepickerDate'] = $this->db->getMinDateFromTable($date_fields['target_table'], $date_fields['time_begin']);
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function stat_tv() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $attribute = $this->getTvDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;
        
        $this->app['filters'] = (array_key_exists('filters', $this->data) ? $this->data['filters'] : array());
        $this->app['allTVLocale'] = $this->db->getTVLocale();

        $list = $this->stat_tv_list_json();
        
        $this->app['allStat'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        $date_fields = $this->getBeginEndPeriod();
        $this->app['minDatepickerDate'] = $this->db->getMinDateFromTable($date_fields['target_table'], $date_fields['time_begin']);

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function stat_tv_archive() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $attribute = $this->getTvArchiveDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $list = $this->stat_tv_archive_list_json();
        
        $this->app['allStat'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        $date_fields = $this->getBeginEndPeriod();
        $this->app['minDatepickerDate'] = $this->db->getMinDateFromTable($date_fields['target_table'], $date_fields['time_begin']);

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function stat_timeshift() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $attribute = $this->getTimeShiftDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $list = $this->stat_timeshift_list_json();
        
        $this->app['allStat'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        $date_fields = $this->getBeginEndPeriod();
        $this->app['minDatepickerDate'] = $this->db->getMinDateFromTable($date_fields['target_table'], $date_fields['time_begin']);

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function stat_moderators() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $task_report_state = $this->taskAllState;
        unset($task_report_state[0]);
        unset($task_report_state[3]);
        unset($task_report_state[4]);
        $this->app['taskType'] = $this->taskType;
        $this->app['taskState'] = $task_report_state;
        $this->app["allTaskState"] = $this->taskAllState;
        $this->app['videoQuality'] = $this->videoQuality;
        $this->app['taskAdmin'] = $this->db->getAdmins(); // getAdmins( $user_id ) !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        
        
        $list = $this->stat_moderators_list_json();
        
        $this->app['allData'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        
        $this->app['task_type_title'] = $this->getTaskTitle($list['table']);
        $this->app['task_type'] = $list['table'];
        $this->app['taskStateColor'] = $this->stateColor;

        if (empty($this->data['filters']['task_type'])) {
            if (empty($this->data['filters'])) {
                $this->data['filters'] = array('task_type' => 'moderator_tasks');
            } else {
                $this->data['filters']['task_type'] = 'moderator_tasks';
            }
            $dropdown_filters = '';
        } else {
            $dropdown_filters = "-filters-{$this->data['filters']['task_type']}";
        }
        $attribute = $this->getModeratorsDropdownAttribute();
        $this->checkDropdownAttribute($attribute, $dropdown_filters);
        $this->app['dropdownAttribute'] = $attribute;
        
        if ($this->data['filters']['task_type'] == 'moderator_tasks'){
            $this->app['allVideoDuration'] = $list['videotime'];                              //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        }
        $allArhivedate = $this->db->getArhiveIDs((($this->data['filters']['task_type'] == 'moderator_tasks')? 'tasks': 'karaoke') . '_archive');
        $this->app['allArhivedate'] = array_reverse($allArhivedate);
        $this->app['filters'] = $this->data['filters'];

        $date_fields = $this->getBeginEndPeriod();
        $this->app['minDatepickerDate'] = $this->db->getMinDateFromTable($date_fields['target_table'], $date_fields['time_begin']);

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function stat_abonents() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if (empty($this->data['filters']['abon_to'])) {
            if (empty($this->data['filters'])) {
                $this->data['filters'] = array('abon_to' => 'tv');
            } else {
                $this->data['filters']['abon_to'] = 'tv';
            }
            $dropdown_filters = '';
        } else {
            $dropdown_filters = "-filters-{$this->data['filters']['abon_to']}";
        }
        

        $this->app['filters'] = $this->data['filters'];
        $filter = $this->app['filters']['abon_to'];

        $this->app['allAbonentStat'] = $this->allAbonentStat;

        $attr_func = "getAbonent" . ucfirst($filter) . "DropdownAttribute";

        $attribute = $this->$attr_func();
        $this->checkDropdownAttribute($attribute, $dropdown_filters);
        $this->app['dropdownAttribute'] = $attribute;

        $list = $this->stat_abonents_list_json();
        
        $this->app['allStat'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        $date_fields = $this->getBeginEndPeriod();
        $this->app['minDatepickerDate'] = $this->db->getMinDateFromTable($date_fields['target_table'], $date_fields['time_begin']);

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function stat_abonents_unactive() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if (empty($this->data['filters']['no_active_abonent'])) {
            if (empty($this->data['filters'])) {
                $this->data['filters'] = array('no_active_abonent' => 'tv');
            } else {
                $this->data['filters']['no_active_abonent'] = 'tv';
            }
            $dropdown_filters = '';
        } else {
            $dropdown_filters = "-filters-{$this->data['filters']['no_active_abonent']}";
        }

        $this->app['filters'] = $this->data['filters'];
        $filter = $this->app['filters']['no_active_abonent'];

        $this->app['allNoActiveAbonentStat'] = $this->allNoActiveAbonentStat;

        $attr_func = "getNoActiveAbonent" . ucfirst($filter) . "DropdownAttribute";

        $attribute = $this->$attr_func();
        $this->checkDropdownAttribute($attribute, $dropdown_filters);
        $this->app['dropdownAttribute'] = $attribute;

        $list = $this->stat_abonents_unactive_list_json();
        
        $this->app['allStat'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        $date_fields = $this->getBeginEndPeriod();
        $this->app['minDatepickerDate'] = $this->db->getMinDateFromTable($date_fields['target_table'], $date_fields['time_begin']);

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function stat_claims() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $attribute = $this->getClaimsDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;
        
        $list = $this->stat_claims_list_json();
        
        $this->app['allStat'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        $date_fields = $this->getBeginEndPeriod();
        $this->app['minDatepickerDate'] = $this->db->getMinDateFromTable($date_fields['target_table'], $date_fields['time_begin']);

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    public function stat_claims_logs(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $attribute = $this->getClaimsLogsDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;
        
        $list = $this->stat_claims_logs_list_json();
        
        $this->app['allStat'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        $date_fields = $this->getBeginEndPeriod();
        $this->app['minDatepickerDate'] = $this->db->getMinDateFromTable($date_fields['target_table'], $date_fields['time_begin']);

        $this->app['breadcrumbs']->addItem($this->setLocalization('Complaints statistics'), $this->workURL . "/" . $this->app['controller_alias'] ."/stat-claims");
        $this->app['breadcrumbs']->addItem($this->setLocalization('Complaints log'));
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    //----------------------- ajax method --------------------------------------
    
    public function stat_video_list_json($param = array()) {
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );
        
        $like_filter = array();
        $filters = $this->getStatisticsFilters($like_filter);
        
        $func_alias = ucfirst((!empty($filters['stat_to']) && $filters['stat_to'] != 'main' ? $filters['stat_to']: "all"));
               
        $filds_for_select = $this->{"getVideo{$func_alias}Fields"}();
                
        $error = "Error";
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        unset($filters['stat_to']);
        unset($filters['no_active_abonent']);
        unset($filters['abon_to']);
        unset($filters['task_type']);

        $query_param['where'] = array_merge($query_param['where'], $filters);

        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
//            $query_param['select'][] = 'id';
        }

        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);
                
        $response['recordsTotal'] = $this->db->getVideoStatTotalRows($func_alias);
        $response["recordsFiltered"] = $this->db->getVideoStatTotalRows($func_alias, $query_param['where'], $query_param['like']);

        if ($func_alias != 'Genre') {
            if (empty($query_param['limit']['limit'])) {
                $query_param['limit']['limit'] = 50;
            } elseif ($query_param['limit']['limit'] == -1) {
                $query_param['limit']['limit'] = FALSE;
            }
        }
        
        $response["data"] = $this->db->{"getVideoStat{$func_alias}List"}($query_param);
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;

        while (list($num, $row) = each($response["data"])){
            if ($func_alias == 'Genre'){
                $row['title'] = $this->mb_ucfirst($row['title']);
                if ($row['total_movies'] == 0){
                    $response["data"][$num]['ratio'] = 0;
                } elseif ($row['played_movies'] != 0) {
                    $response["data"][$num]['ratio'] = round(($row['played_movies'] / $row['total_movies'])*100, 2);
                }
                $response["data"][$num]['title'] =  $this->setLocalization($row['title']);
            } else {
                $datekey = (array_key_exists('date', $row) ? 'date': 'last_played');
                $response["data"][$num][$datekey] = (int)strtotime($response["data"][$num][$datekey]);
            }
        }
        
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
    
    public function stat_abonents_unactive_list_json($param = array()) {
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );
        
        $like_filter = array();
        $filters = $this->getStatisticsFilters($like_filter);
        
        $func_alias = ucfirst((!empty($filters['no_active_abonent']) ? $filters['no_active_abonent']: "tv"));
               
        $filds_for_select = $this->{"getNoActiveAbonent{$func_alias}Fields"}();
                
        $error = "Error";
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        unset($filters['stat_to']);
        unset($filters['no_active_abonent']);
        unset($filters['abon_to']);
        unset($filters['task_type']);

        $query_param['where'] = array_merge($query_param['where'], $filters);
        
        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
//            $query_param['select'][] = 'id';
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);
                
        $response['recordsTotal'] = $this->db->getNoActiveAbonentTotalRows($func_alias);
        $response["recordsFiltered"] = $this->db->getNoActiveAbonentTotalRows($func_alias, $query_param['where'], $query_param['like']);

        if ($func_alias != 'Genre') {
            if (empty($query_param['limit']['limit'])) {
                $query_param['limit']['limit'] = 50;
            } elseif ($query_param['limit']['limit'] == -1) {
                $query_param['limit']['limit'] = FALSE;
            }
        }
        
        $response["data"] = $this->db->{"getNoActiveAbonent{$func_alias}List"}($query_param);
        $response["data"] = array_map(function($row){
            $row['time_last_play'] = (int) strtotime($row['time_last_play']);
            $row['time_last_play'] = ($row['time_last_play'] <= 0 ) ? '0000-00-00': $row['time_last_play'];
            return $row;
        }, $response["data"]);
        
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
               
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
    
    public function stat_claims_list_json($param = array()) {
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );

        $like_filter = array();
        $filters = $this->getStatisticsFilters($like_filter);
               
        $filds_for_select = $this->getFieldFromArray($this->getClaimsDropdownAttribute(), 'name');
                
        $error = $this->setLocalization("Error");
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        unset($filters['stat_to']);
        unset($filters['no_active_abonent']);
        unset($filters['abon_to']);
        unset($filters['task_type']);

        $query_param['where'] = array_merge($query_param['where'], $filters);

        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
//            $query_param['select'][] = 'id';
        }
        
        if (($search = array_search('date', $query_param['select'])) !== FALSE) {
            $query_param['select'][$search] = 'CAST(`date` as CHAR) as `date`';
        }
                
        $response['recordsTotal'] = $this->db->getDailyClaimsTotalRows();
        $response["recordsFiltered"] = $this->db->getDailyClaimsTotalRows($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        
        $response["data"] = $this->db->getDailyClaimsList($query_param);
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
               
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
    
    public function stat_claims_logs_list_json($param = array()) {
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );

        $filds_for_select = $this->getFieldFromArray($this->getClaimsLogsDropdownAttribute(), 'name');
                
        $error = $this->setLocalization("Error");
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);
        
        if (!empty($param['type'])) {
            if (strpos($param['type'], 'epg') !== FALSE) {
                $param['media_type'] = 'itv';
            } else {
                $tmp = explode('_', $param['type']);
                $param['media_type'] = $tmp[0];//($tmp[0] == 'vclub'? 'video': $tmp[0]);
                $param['type'] = $tmp[1];
            }
        }

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        if (!empty($param['type'])) {
            $query_param['where']['`type`'] = $param['type'];
        }
        if (!empty($param['media_type'])) {
            $query_param['where']['`media_type`'] = $param['media_type'];
        }

        if (!isset($query_param['like'])) {
            $query_param['like'] = array();
        }
        if (!empty($param['date'])) {
            $query_param['like']['M_C_L.`added`'] = $param['date']."%";
        }

        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } 
        
        if (($search = array_search('name', $query_param['select'])) !== FALSE) {
            $query_param['select'][$search] = 'if(isnull(I.`name`), if(isnull(K.`name`), if(isnull(V.`name`), "undefined", V.`name`), K.`name`),I.`name`) as `name`';
        }
        if (($search = array_search('added', $query_param['select'])) !== FALSE) {
            $query_param['select'][$search] = 'CAST(M_C_L.`added` as CHAR) as `added`';
        }
        $query_param['select'][] = "M_C_L.uid";
        
        $response['recordsTotal'] = $this->db->getClaimsLogsTotalRows();
        $response["recordsFiltered"] = $this->db->getClaimsLogsTotalRows($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        
        $response["data"] = $this->db->getClaimsLogsList($query_param);
        
        $response["data"] = array_map(function($row){
            $row['added'] = (int) strtotime($row['added']);
            return $row;
        }, $response["data"]);
        
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
               
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
    
    public function stat_tv_archive_list_json() {
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );

        $like_filter = array();
        $filters = $this->getStatisticsFilters($like_filter);

        $filds_for_select = $this->getFieldFromArray($this->getTvArchiveDropdownAttribute(), 'name');
                
        $error = "Error";
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        unset($filters['stat_to']);
        unset($filters['no_active_abonent']);
        unset($filters['abon_to']);
        unset($filters['task_type']);

        $query_param['where'] = array_merge($query_param['where'], $filters);

        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
//            $query_param['select'][] = 'id';
        }
        
        if (($search = array_search('counter', $query_param['select'])) !== FALSE) {
            $query_param['select'][$search] = 'count(`ch_id`) as `counter`';
        }
        
        if (($search = array_search('total_duration', $query_param['select'])) !== FALSE) {
            $query_param['select'][$search] = 'SUM(`length`) as `total_duration`';
        }
                
        $response['recordsTotal'] = $this->db->getTvArchiveTotalRows();
        $response["recordsFiltered"] = $this->db->getTvArchiveTotalRows($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        
        $response["data"] = $this->db->getTvArchiveList($query_param);
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
               
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
    
    public function stat_timeshift_list_json() {
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );

        $like_filter = array();
        $filters = $this->getStatisticsFilters($like_filter);
               
        $filds_for_select = $this->getFieldFromArray($this->getTvArchiveDropdownAttribute(), 'name');
                
        $error = "Error";
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        unset($filters['stat_to']);
        unset($filters['no_active_abonent']);
        unset($filters['abon_to']);
        unset($filters['task_type']);

        $query_param['where'] = array_merge($query_param['where'], $filters);

        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
//            $query_param['select'][] = 'id';
        }
        
        if (($search = array_search('counter', $query_param['select'])) !== FALSE) {
            $query_param['select'][$search] = 'count(`ch_id`) as `counter`';
        }
        
        if (($search = array_search('total_duration', $query_param['select'])) !== FALSE) {
            $query_param['select'][$search] = 'SUM(`length`) as `total_duration`';
        }
                
        $response['recordsTotal'] = $this->db->getTimeShiftTotalRows();
        $response["recordsFiltered"] = $this->db->getTimeShiftTotalRows($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        
        $response["data"] = $this->db->getTimeShiftList($query_param);
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
               
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
    
    public function stat_abonents_list_json(){
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );
        
        $like_filter = array();
        $filters = $this->getStatisticsFilters($like_filter);
        
        $func_alias = ucfirst((!empty($filters['abon_to']) ? $filters['abon_to']: "tv"));
               
        $filds_for_select = $this->{"getAbonent{$func_alias}Fields"}();
                
        $error = "Error";
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        unset($filters['stat_to']);
        unset($filters['no_active_abonent']);
        unset($filters['abon_to']);
        unset($filters['task_type']);

        $query_param['where'] = array_merge($query_param['where'], $filters);
        
        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
//            $query_param['select'][] = 'id';
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);
                
        $response['recordsTotal'] = $this->db->getAbonentStatTotalRows($func_alias);
        $response["recordsFiltered"] = $this->db->getAbonentStatTotalRows($func_alias, $query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        
        $response["data"] = $this->db->{"getAbonentStat{$func_alias}List"}($query_param);
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        
        if ($func_alias == 'Anec') {
            $response["data"] = array_map(function($row){
                $row['readed'] = (int) strtotime($row['readed']);
            }, $response["data"]);    
        }
        
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function stat_tv_list_json() {
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );

        $like_filter = array();
        $filters = $this->getStatisticsFilters($like_filter);
               
        $filds_for_select = $this->getFieldFromArray($this->getTvDropdownAttribute(), 'name');
                
        $error = "Error";
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        unset($filters['stat_to']);
        unset($filters['no_active_abonent']);
        unset($filters['abon_to']);
        unset($filters['task_type']);

        $query_param['where'] = array_merge($query_param['where'], $filters);
        
        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
//            $query_param['select'][] = 'id';
        }
        
        if (($search = array_search('counter', $query_param['select'])) !== FALSE) {
            $query_param['select'][$search] = 'count(`played_itv`.id) as `counter`';
        }
                       
        $response['recordsTotal'] = $this->db->getTvTotalRows();
        $response["recordsFiltered"] = $this->db->getTvTotalRows($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        
        $response["data"] = $this->db->getTvList($query_param);
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
               
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
    
    public function stat_moderators_list_json(){
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'action' => 'setKaraokeModal',
            'table' => 'moderator_tasks'
        );
        $error = "Error";
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);
        
        $like_filter = array();
        $filter = $this->getStatisticsFilters($like_filter);
        if (!empty($filter['task_type'])) {
            $response['table'] = $filter['task_type'];
        }
        if (!empty($param['task_type'])) {
            $response['table'] = $param['task_type'];  
        }

        unset($filter['task_type']);
        unset($filter['stat_to']);
        unset($filter['no_active_abonent']);
        unset($filter['abon_to']);

        $func = "getFieldsReport" . ucfirst($response['table']);
        $filds_for_select = $this->$func($response['table']);
        

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        /*$query_param['where'] = array_merge($query_param['where'], $filter);*/

        if (empty($query_param['like']) && !empty($like_filter)) {
            $query_param['like'] = $like_filter;
        } elseif (!empty($query_param['like']) && !empty($like_filter)) {
            $query_param['like'] = array_merge($query_param['like'], $like_filter);
        }
        
        $query_param['where'] = array_merge($query_param['where'], $filter);
        $query_param['where']['A.id is not '] = NULL;
        
        if ($response['table'] == 'karaoke') {
            $query_param['where']['done'] = 1;    
        } else {
            $query_param['where']['ended'] = 1;    
        }
        
        $prefix = implode('_', array_map(function($val){ 
            return strtoupper(substr($val, 0, 1));
        }, explode("_", $response['table'])));
        
        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
            $query_param['select'][] = "A.`id` as `user_id`";
            $query_param['select'][] = "(archived<>0) as `archived`";
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);

//        if (!Admin::isPageActionAllowed()){
//            $query_param['where']['A.`id`'] = $this->uid;
//        }
        
        $func = "getJoinedReport" . ucfirst($response['table']);
        $query_param['joined'] = $this->$func();
        
        $func = "getGropByReport" . ucfirst($response['table']);
        $query_param['groupby'] = $this->$func();
        
        $query_param['from'] = "$response[table] as $prefix";
        $query_param['groupby'][] = "$prefix.`id`";
        
        $response['recordsTotal'] = $this->db->getModeratorsStatRowsList($query_param, TRUE);
        $response["recordsFiltered"] = $this->db->getModeratorsStatRowsList($query_param);
        
        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        
        $response['videotime'] = $this->getVideoTime($query_param);
        
        $response['data'] = array_map(function($val){
            $val['state'] = (int)$val['state'];
            $val['start_time'] = (int)  strtotime($val['start_time']);
            $val['end_time'] = (int) strtotime($val['end_time']);
            return $val;
        }, $this->db->getModeratorsStatList($query_param));
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function stat_claims_clean(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['media_type'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'manageClaims';
        /*$data['msg'] = $this->setLocalization("Well done!");*/
        $error = $this->setLocalization('Error');

        if ($this->postData['media_type'] == 'all') {
            $this->db->truncateTable("daily_media_claims");
            $this->db->truncateTable("media_claims");
            $this->db->truncateTable("media_claims_log");
            $error = "";
        } else {
            $query_params = array(
                'select' => array('id', 'date'),
                'like' => array(),
                'order'=>array()
            );

            if ($this->postData['media_type'] == 'epg') {
                $query_params['where'] = array(
                    "no_epg <> 0 OR wrong_epg<>" => 0
                );
            } else {
                $query_params['where'] = array(
                    $this->postData['media_type']."_sound <> 0 OR ".$this->postData['media_type']."_video<>" => 0
                );
            }

            $date = $this->db->getDailyClaimsList($query_params);
            if (!empty($date) && is_array($date)) {
                $query_params['select'] = array('M_C_L.id as `id`', 'M_C_L.media_id as `media_id`');
                if ($this->postData['media_type'] == 'epg') {
                    $query_params['where'] = array(
                        "M_C_L.media_type" => 'itv',
                        "M_C_L.`type` = 'no_epg' OR M_C_L.`type` = " => 'wrong_epg'
                    );
                } else {
                    $query_params['where'] = array(
                        "media_type" => $this->postData['media_type'],
                        "(M_C_L.`type` = 'sound' OR M_C_L.`type` = 'video') AND '1'=" => '1'
                    );
                }

                $like = array_map(function($row){return "$row%";}, $this->getFieldFromArray($date, 'date'));
                $like_ctr = '';
                for($i = 0; $i <= count($like) - 2; $i++) {
                    $like_ctr .= ' M_C_L.`added` LIKE "' . $like[$i] . '" OR ';
                }
                $like_ctr .= " M_C_L.`added` ";
                $query_params['like'][$like_ctr] = $like[count($like) - 1];
                $log = $this->db->getClaimsLogsList($query_params);

                if ($this->postData['media_type'] == 'epg') {
                    $new_values = array(
                        "no_epg" => 0,
                        "wrong_epg" => 0
                    );
                } else {
                    $new_values = array(
                        $this->postData['media_type']."_sound" => 0,
                        $this->postData['media_type']."_video" => 0
                    );
                }

                if ($this->db->updateDailyClaims($new_values, array('id'=>$this->getFieldFromArray($date, 'id')))) {
                    $this->db->cleanDailyClaims();
                }

                if ($this->postData['media_type'] != 'epg') {
                    $new_values = array(
                        "sound_counter" => 0,
                        "video_counter" => 0
                    );
                }

                if ($this->db->updateMediaClaims($new_values, array('media_id'=>$this->getFieldFromArray($log, 'media_id')), array('media_type' => ($this->postData['media_type'] == 'epg' ? 'itv': $this->postData['media_type'])))) {
                    $this->db->cleanMediaClaims();
                }

                $this->db->deleteClaimsLogs(array('id'=>$this->getFieldFromArray($log, 'id')));
                $error = '';
            } else {
                $data['msg'] = $this->setLocalization("Nothing in this category");
                $error = '';
            }
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    //------------------------ service method ----------------------------------

    private function getStatisticsFilters(&$like_filter) {
        $return = array();

        if (!empty($this->data['filters'])) {
            if (!empty($this->data['filters']['stat_to'])) {
                $return['stat_to'] = $this->data['filters']['stat_to'];
            } else {
                $return['stat_to'] = 'main';
            }
            
            if (!empty($this->data['filters']['no_active_abonent'])) {
                $return['no_active_abonent'] = $this->data['filters']['no_active_abonent'];
            } else {
                $return['no_active_abonent'] = 'tv';
            }
            
            if (!empty($this->data['filters']['abon_to'])) {
                $return['abon_to'] = $this->data['filters']['abon_to'];
            } else {
                $return['abon_to'] = 'tv';
            }
            
            if (!empty($this->data['filters']['user_locale'])) {
                $return['user_locale'] = $this->data['filters']['user_locale'];
            }
            
            if (array_key_exists('task_type', $this->data['filters'])) {
                $return['task_type'] = $this->data['filters']['task_type'];
            } else {
                $return['task_type'] = 'moderator_tasks';
            }
                       
            if (array_key_exists('state', $this->data['filters']) && !empty($this->data['filters']['state'])) {
                
                $state = (int)$this->data['filters']['state'];
                if ($state != 5) {
                    if ($return['task_type'] == 'karaoke'){
                        $return["if(done=0 and archived=0 and (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(added))>864000, 3, done)="] = ((int)$this->data['filters']['state']) - 1;
                    } else{
                        $return["if(ended=0 and archived=0 and (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(start_time))>864000, 3, ended + rejected)="] = ((int)$this->data['filters']['state']) - 1;
                    }
                } else {
                    $return["`archived`<>"] = 0;
                }
            }
            
            if (array_key_exists('video_quality', $this->data['filters']) && !empty($this->data['filters']['video_quality']) && $return['task_type'] == 'moderator_tasks') {
                $return["`hd`"] = ((int)$this->data['filters']['video_quality']) - 1;
            }

            if (array_key_exists('to_user', $this->data['filters']) && !empty($this->data['filters']['to_user'])) {
                $return['A.`id`'] = $this->data['filters']['to_user'];
            }

            if (array_key_exists('archived', $this->data['filters']) && !empty($this->data['filters']['archived'])) {
                $return['`archived`'] = $this->data['filters']['archived'];
            }

            extract($this->getBeginEndPeriod());

            if (array_key_exists('interval_from', $this->data['filters']) && $this->data['filters']['interval_from']!= 0 && !empty($time_begin)) {
                $date = \DateTime::createFromFormat('d/m/Y', $this->data['filters']['interval_from']);
                $return["UNIX_TIMESTAMP($time_begin)>="] = $date->getTimestamp();
            }

            if (array_key_exists('interval_to', $this->data['filters']) && $this->data['filters']['interval_to']!= 0 && !empty($time_end)) {
                $date = \DateTime::createFromFormat('d/m/Y', $this->data['filters']['interval_to']);
                $return["UNIX_TIMESTAMP($time_end)<="] = $date->getTimestamp();
            }

            $this->app['filters'] = $this->data['filters'];
        } else {
            $this->app['filters'] = array();
        }
        return $return;
    }
    
    private function getVideoAllDropdownAttribute() {
        return array(
            array('name' => 'id',               'title' => $this->setLocalization('ID'),            'checked' => TRUE),
            array('name' => 'name',             'title' => $this->setLocalization('Title'),         'checked' => TRUE),
            array('name' => 'count',            'title' => $this->setLocalization('Views lifetime'),'checked' => TRUE),
            array('name' => 'counter',          'title' => $this->setLocalization('Views last month'),'checked' => TRUE),
            array('name' => 'last_played',      'title' => $this->setLocalization('Last viewed date'),'checked' => TRUE),
            array('name' => 'count_storages',   'title' => $this->setLocalization('Number of copies'),'checked' => TRUE)
        );
    }
    
    private function getVideoDailyDropdownAttribute() {
        return array(
            array('name' => 'id',           'title' => $this->setLocalization('ID'),    'checked' => TRUE),
            array('name' => 'date',         'title' => $this->setLocalization('Day'),   'checked' => TRUE),
            array('name' => 'count',        'title' => $this->setLocalization('By day'),'checked' => TRUE)
        );
    }
    
    private function getVideoGenreDropdownAttribute() {
        return array(
            array('name' => 'title',        'title' => $this->setLocalization('Genre'),                 'checked' => TRUE),
            array('name' => 'played_movies','title' => $this->setLocalization('Overall movies'),        'checked' => TRUE),
            array('name' => 'total_movies', 'title' => $this->setLocalization('Views quantity'),        'checked' => TRUE),
            array('name' => 'ratio',        'title' => $this->setLocalization('Genre popularity') .', %','checked' => TRUE)
        );
    }
    
    private function getVideoAllFields(){
    return array(
            "id" => "`video`.`id` as `id`",
            "name" => "`video`.`name` as `name`",
            "count" => "`video`.`count` as `count`",
            "counter" => "(`video`.count_second_0_5 + `video`.count_first_0_5) as `counter`",
            "last_played" => "CAST(`video`.`last_played` as CHAR) as `last_played`",
            "count_storages" => "(select count(*) from `storage_cache` as S_C where S_C.`status` = 1 and S_C.`media_type` = 'vclub' and S_C.`media_id` = `video`.`id`) as `count_storages`"
        );
    }
    
    private function getVideoDailyFields(){
    return array(
            "id" => "`daily_played_video`.`id` as `id`",
            "date" => "CAST(`daily_played_video`.`date` as CHAR) as `date`",
            "count" => "`daily_played_video`.`count` as `count`"
        );
    }
    
    private function getVideoGenreFields(){
        $date_obj =  new \DateTime( 'midnight 30 days ago' );
    return array(
            "title" => "`genre`.`title` as `title`",
            "played_movies" => "(select count(*) from `video` as V where V.genre_id_1 = `genre`.id or V.genre_id_2 =`genre`.id or V.genre_id_3 = `genre`.id or V.genre_id_4 = `genre`.id) as `played_movies`",
            "total_movies" => "(select count(*) from `played_video` as P_V left join video as V on V.id=P_V.video_id where `playtime`> '{$date_obj->format('Y-m-d H:i:s')}' and (V.genre_id_1 = `genre`.id or V.genre_id_2 =`genre`.id or V.genre_id_3 = `genre`.id or V.genre_id_4 = `genre`.id)) as `total_movies`",
            "ratio" => "0 as `ratio`"
        );
    }
    
    private function getNoActiveAbonentTvDropdownAttribute() {
        return array(
            array('name' => 'id',               'title' => $this->setLocalization('ID'),            'checked' => TRUE),
            array('name' => 'mac',              'title' => $this->setLocalization('MAC'),           'checked' => TRUE),
            array('name' => 'time_last_play',   'title' => $this->setLocalization('Last view TV'),  'checked' => TRUE)
        );
    }
    
    private function getNoActiveAbonentVideoDropdownAttribute() {
        return array(
            array('name' => 'id',               'title' => $this->setLocalization('ID'),            'checked' => TRUE),
            array('name' => 'mac',              'title' => $this->setLocalization('MAC'),           'checked' => TRUE),
            array('name' => 'time_last_play',   'title' => $this->setLocalization('Last view movie'),'checked' => TRUE)
        );
    }
    
    private function getNoActiveAbonentTvFields(){
    return array(
            "id" => "`users`.`id` as `id`",
            "mac" => "`users`.`mac` as `mac`",
            "time_last_play" => "CAST(`users`.`time_last_play_tv` as CHAR) as `time_last_play`",
        );
    }
    
    private function getNoActiveAbonentVideoFields(){
    return array(
            "id" => "`users`.`id` as `id`",
            "mac" => "`users`.`mac` as `mac`",
            "time_last_play" => "CAST(`users`.`time_last_play_video` as CHAR) as `time_last_play`",
        );
    }
    
    private function getClaimsDropdownAttribute() {
        return array(
            array('name' => 'date',             'title' => $this->setLocalization('Date'),              'checked' => TRUE),
            array('name' => 'vclub_sound',      'title' => $this->setLocalization('Video-club sound'),  'checked' => TRUE),
            array('name' => 'vclub_video',      'title' => $this->setLocalization('Video-club video'),  'checked' => TRUE),
            array('name' => 'itv_sound',        'title' => $this->setLocalization('TV sound'),          'checked' => TRUE),
            array('name' => 'itv_video',        'title' => $this->setLocalization('TV video'),          'checked' => TRUE),
            array('name' => 'karaoke_sound',    'title' => $this->setLocalization('Karaoke sound'),     'checked' => TRUE),
            array('name' => 'karaoke_video',    'title' => $this->setLocalization('Karaoke video'),     'checked' => TRUE),
            array('name' => 'no_epg',           'title' => $this->setLocalization('No EPG'),            'checked' => TRUE),
            array('name' => 'wrong_epg',        'title' => $this->setLocalization('EPG does not match'),'checked' => TRUE)
        );
    }
    
    private function getClaimsLogsDropdownAttribute() {
        return array(
            array('name' => 'media_type',   'title' => $this->setLocalization('Category'),          'checked' => TRUE),
            array('name' => 'name',         'title' => $this->setLocalization('Object of complaint'),'checked' => TRUE),
            array('name' => 'type',         'title' => $this->setLocalization('Type'),              'checked' => TRUE),
            array('name' => 'mac',          'title' => $this->setLocalization('Author'),            'checked' => TRUE),
            array('name' => 'added',        'title' => $this->setLocalization('Date'),              'checked' => TRUE)
        );
    }
    
    private function getTvArchiveDropdownAttribute() {
        return array(
            array('name' => 'ch_id',            'title' => $this->setLocalization('ID'),                        'checked' => TRUE),
            array('name' => 'name',             'title' => $this->setLocalization('Title'),                     'checked' => TRUE),
            array('name' => 'counter',          'title' => $this->setLocalization('Views quantity'),            'checked' => TRUE),
            array('name' => 'total_duration',   'title' => $this->setLocalization('Entire time of views, sec'), 'checked' => TRUE)
        );
    }
    
    private function getTimeShiftDropdownAttribute() {
        return array(
            array('name' => 'ch_id',            'title' => $this->setLocalization('ID'),                        'checked' => TRUE),
            array('name' => 'name',             'title' => $this->setLocalization('Title'),                     'checked' => TRUE),
            array('name' => 'counter',          'title' => $this->setLocalization('Views quantity'),            'checked' => TRUE),
            array('name' => 'total_duration',   'title' => $this->setLocalization('Entire time of views, sec'), 'checked' => TRUE)
        );
    }
    
    private function getAbonentTvDropdownAttribute() {
        return array(
            array('name' => 'id',       'title' => $this->setLocalization('ID'),            'checked' => TRUE),
            array('name' => 'mac',      'title' => $this->setLocalization('MAC'),           'checked' => TRUE),
            array('name' => 'counter',  'title' => $this->setLocalization('Views quantity'),'checked' => TRUE)
        );
    }
    
    private function getAbonentVideoDropdownAttribute() {
        return array(
            array('name' => 'id',       'title' => $this->setLocalization('ID'),            'checked' => TRUE),
            array('name' => 'mac',      'title' => $this->setLocalization('MAC'),           'checked' => TRUE),
            array('name' => 'counter',  'title' => $this->setLocalization('Views quantity'),'checked' => TRUE)
        );
    }
    
    private function getAbonentAnecDropdownAttribute() {
        return array(
            array('name' => 'id',       'title' => $this->setLocalization('ID'),            'checked' => TRUE),
            array('name' => 'mac',      'title' => $this->setLocalization('MAC'),           'checked' => TRUE),
            array('name' => 'counter',  'title' => $this->setLocalization('Views quantity'),'checked' => TRUE),
            array('name' => 'readed',   'title' => $this->setLocalization('Last view'),     'checked' => TRUE)
        );
    }
    
    private function getAbonentTvFields(){
    return array(
            "id" => "`users`.`id` as `id`",
            "mac" => "`users`.`mac` as `mac`",
            "counter" => "count(`played_itv`.`id`) as `counter`"
        );
    }
    
    private function getAbonentVideoFields(){
    return array(
            "id" => "`users`.`id` as `id`",
            "mac" => "`users`.`mac` as `mac`",
            "counter" => "count(`played_video`.`id`) as `counter`"
        );
    }
    
    private function getAbonentAnecFields(){
    return array(
            "id" => "`readed_anec`.`id` as `id`",
            "mac" => "`readed_anec`.`mac` as `mac`",
            "counter" => "count(`readed_anec`.`mac`) as `counter`",
            "readed" => "CAST(max(readed) as CHAR) as `readed`"
        );
    }
    
    private function getTvDropdownAttribute() {
        return array(
            array('name' => 'itv_id',   'title' => $this->setLocalization('ID'),            'checked' => TRUE),
            array('name' => 'name',     'title' => $this->setLocalization('Title'),         'checked' => TRUE),
            array('name' => 'counter',  'title' => $this->setLocalization('Views quantity'),'checked' => TRUE)
        );
    }
    
    private function getModeratorsDropdownAttribute() {
        return array(
            array('name'=>'id',             'title'=>$this->setLocalization('Order'),       'checked' => TRUE),
            array('name'=>'type',           'title'=>$this->setLocalization('Type'),        'checked' => FALSE),
            array('name'=>'start_time',     'title'=>$this->setLocalization('Created'),     'checked' => TRUE),
            array('name'=>'end_time',       'title'=>$this->setLocalization('Completed'),   'checked' => TRUE),
            array('name'=>'name',           'title'=>$this->setLocalization('Title'),       'checked' => TRUE),
            array('name'=>'video_quality',  'title'=>$this->setLocalization('Quality'),     'checked' => TRUE),
            array('name'=>'duration',       'title'=>$this->setLocalization('Length, min'), 'checked' => TRUE),
            array('name'=>'to_user_name',   'title'=>$this->setLocalization('Moderator'),   'checked' => TRUE),
            array('name'=>'state',          'title'=>$this->setLocalization('State'),       'checked' => TRUE)
        );
    }
    
    private function getFieldsReportModerator_tasks($table = ''){
        
        return array(
            "user_id"       => "A.`id` as `user_id`",
            "id"            => "M_T.`id` as `id`",
            "type"          => "'{$this->getTaskTitle($table)}'as `type`",
            "name"          => "V.`name` as `name`",
            "to_user_name"  => "A.`login` as `to_user_name`",
            "start_time"    => "CAST(M_T.`start_time` as CHAR ) as `start_time`",
            "state"         => "if(ended=0 and archived=0 and (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(start_time))>864000, 3, M_T.`ended` + M_T.rejected) as `state`",
            "end_time"      => "CAST(M_T.`end_time` as CHAR ) as `end_time`",
            "video_quality" => "if(V.hd = 0, 'SD', 'HD') as `video_quality`",
            "duration"      => "V.`time` as `duration`",
            "archived"      => "(archived<>0) as `archived`"
                    
        );
    }
    
    private function getFieldsReportKaraoke($table = ''){
        return array(
            "user_id"       => "A.`id` as `user_id`",
            "id"            => "K.`id` as `id`",
            "type"          => "'{$this->getTaskTitle($table)}'as `type`",
            "name"          => "concat_ws(' - ', K.`singer`, K.`name`) as `name`",
            "to_user_name"  => "A.`login` as `to_user_name`",
            "start_time"    => "CAST(K.`added` as CHAR ) as `start_time`",
            "state"         => "if(K.done=0 and K.archived=0 and (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(K.added))>864000, 3, K.done) as `state`",
            "end_time"      => "CAST(K.`done_time` as CHAR ) as `end_time`", 
            "video_quality" => "'-' as `video_quality`",
            "duration"      => "'-' as `duration`",
            "archived"      => "(archived<>0) as `archived`"
        );
    }
    
    private function getJoinedReportModerator_tasks(){
        return array(
            '`administrators` as A'         =>array('left_key'=>'M_T.`to_usr`',     'right_key'=>'A.`id`', 'type'=>'LEFT'),
            '`video` as V'                  =>array('left_key'=>'M_T.`media_id`',   'right_key'=>'V.`id`', 'type'=>'INNER'),
            '`moderators_history` as M_H'   =>array('left_key'=>'M_T.`id`',         'right_key'=>'M_H.`task_id` and M_T.`to_usr` = M_H.`to_usr`', 'type'=>'LEFT')
        );
//        unset($return['`moderators_history` as M_H']);
    }
    
    private function getJoinedReportKaraoke(){
        return array(
            '`administrators` as A' => array('left_key'=>'K.`add_by`', 'right_key'=>'A.`id`', 'type'=>'LEFT')
        );
    }
    
    private function getGropByReportModerator_tasks(){
        return array();
    }
    
    private function getGropByReportKaraoke(){
        return array();
    }
    
    private function getVideoTime($params){
        if (strpos($params['from'], 'moderator_tasks') !== FALSE) {
            
            
            $return['hd_time'] = -1;
            $return['sd_time'] = -1;
            unset($params['select']);
            unset($params['groupby']);
            $params['select'][] = "sum(V.`time`) as `summtime`";
            $params['where']['ended'] = 1;
            $params['where']['rejected'] = 0;
            
            $params['limit'] = array();
            if (!empty($this->data['filters']['video_quality']) && $this->data['filters']['video_quality'] == 2) {
                $result = $this->db->getModeratorsStatList($params);
                $return['hd_time'] = $result[0]['summtime'];    
            }
            
            if (!empty($this->data['filters']['video_quality']) && $this->data['filters']['video_quality'] == 1) {
                $result = $this->db->getModeratorsStatList($params);
                $return['sd_time'] = $result[0]['summtime'];    
            }
            if (empty($this->data['filters']['video_quality'])) {
                $params['where']["`hd`"] = 0; 
                $result = $this->db->getModeratorsStatList($params);
                $return['sd_time'] = $result[0]['summtime'];    
                $params['where']["`hd`"] = 1; 
                $result = $this->db->getModeratorsStatList($params);
                $return['hd_time'] = $result[0]['summtime']; 
            }
            
            return $return;
        }
        
        return -1;
    }
    
    private function getTaskTitle($param) {
        foreach ($this->taskType as $row) {
            if ($row['id'] == $param) {
                return $row['title'];
            }
        }
        return '';
    }

    private function getBeginEndPeriod(){
        $return = array('time_end' => '', 'time_begin'=>'', 'target_table'=>'');
        switch (str_replace('-list-json', '', $this->app['action_alias'])) {
            case 'stat-moderators': {
                $return['time_end'] = (!empty($this->data['filters']['task_type']) && $this->data['filters']['task_type'] == 'karaoke')? 'done_time': 'end_time';
                $return['time_begin'] = (!empty($this->data['filters']['task_type']) && $this->data['filters']['task_type'] == 'karaoke')? 'done_time': 'end_time';
                $return['target_table'] = '';
                break;
            }
            case 'stat-video': {
                if (empty($this->data['filters']['stat_to']) || $this->data['filters']['stat_to'] != 'genre') {
                    $return['time_end'] = $return['time_begin'] = empty($this->data['filters']['stat_to']) || $this->data['filters']['stat_to'] != 'daily'? 'last_played': 'date';
                    $return['target_table'] = empty($this->data['filters']['stat_to']) || $this->data['filters']['stat_to'] != 'daily'? 'video': 'daily_played_video';
                }
                break;
            }
            case 'stat-tv': {
                $return['time_end'] = $return['time_begin'] = 'playtime';
                $return['target_table'] = 'played_itv';
                break;
            }
            case 'stat-tv-archive': {
                $return['time_end'] = $return['time_begin'] = 'playtime';
                $return['target_table'] = 'played_tv_archive';
                break;
            }
            case 'stat-timeshift': {
                $return['time_end'] = $return['time_begin'] = 'playtime';
                $return['target_table'] = 'played_timeshift';
                break;
            }
            case 'stat-abonents': {
                if (empty($this->data['filters']['abon_to']) || $this->data['filters']['abon_to'] == 'tv') {
                    $return['time_end'] = $return['time_begin'] = 'played_itv.playtime';
                    $return['target_table'] = 'played_itv';
                } elseif ($this->data['filters']['abon_to'] == 'video') {
                    $return['time_end'] = $return['time_begin'] = 'played_video.playtime';
                    $return['target_table'] = 'played_video';
                } else{
                    $return['time_end'] = $return['time_begin'] = 'readed';
                    $return['target_table'] = 'readed_anec';
                }
                break;
            }
            case 'stat-abonents-unactive': {
                $return['time_end'] = $return['time_begin'] = '`users`.`time_last_play_tv`';
                $return['target_table'] = 'users';
                break;
            }
            case 'stat-claims': {
                $return['time_end'] = $return['time_begin'] = 'date';
                $return['target_table'] = 'daily_media_claims';
                break;
            }
        }
        return $return;
    }
} 	 	 	 	 	 	 	 	