<?php

namespace Model;

class TariffsModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }

    public function getTotalRowsTariffsList($where = array(), $like = array()) {
        $this->mysqlInstance->count()->from('services_package')->where($where);
        if (!empty($like)) {
            $this->mysqlInstance->like($like, 'OR');
        }
        return $this->mysqlInstance->get()->counter();
    }
   
    public function getTariffsList($param) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('services_package');//->join('users', 'tariffs.uid', 'users.id', 'LEFT')
        if (array_key_exists('where', $param)) {
            $this->mysqlInstance->where($param['where']);
        }
        if (array_key_exists('like', $param)) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (array_key_exists('order', $param)) {
            $this->mysqlInstance->orderby($param['order']);
        }
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], (array_key_exists('offset', $param['limit'])? $param['limit']['offset']: NULL));
        }

        return $this->mysqlInstance->get()->all();
    }
    
    public function getUserCountForPackage($package_id){
        return $this->mysqlInstance->query("SELECT SUM(
                                        (SELECT COUNT(*)
                                        FROM users
                                        WHERE (tariff_plan_id = T_P.id) || 
                                              IF(T_P.user_default, tariff_plan_id = 0, 0))
                                              ) AS user_cont
                                    FROM
                                        package_in_plan AS P_P
                                        LEFT JOIN tariff_plan AS T_P ON P_P.plan_id = T_P.id
                                    WHERE
                                        optional = 0 AND package_id = $package_id")->first('user_cont');
    }

    public function getUserCountForSubscription($package_id){
        return $this->mysqlInstance->from('user_package_subscription')->where(array('package_id' => $package_id))->count()->get()->counter();
    }
    
    public function deletePackageById($package_id) {
        return $this->mysqlInstance->delete('services_package', array('id' => $package_id))->total_rows();
    }
    
    public function deleteServicesById($package_id) {
        return $this->mysqlInstance->delete('service_in_package', array('package_id' => $package_id))->total_rows();
    }
    
    public function getPackageById($package_id) {
        return $this->mysqlInstance->from('service_in_package')->where(array('package_id' => $package_id))->get()->all('service_id');
    }
    
    public function updatePackage($param, $id){
        return $this->mysqlInstance->update('services_package', $param, array('id'=>$id))->total_rows() || 1;
    }

    public function insertPackage($param){
        return $this->mysqlInstance->insert('services_package', $param)->insert_id();
    }

    public function insertServices($param){
        return $this->mysqlInstance->insert('service_in_package', $param)->insert_id();
    }
    
    public function getTariffPlansList($param) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('tariff_plan');//->join('users', 'tariffs.uid', 'users.id', 'LEFT')

        if (array_key_exists('where', $param)) {
            $this->mysqlInstance->where($param['where']);
        }
        if (array_key_exists('like', $param)) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (array_key_exists('order', $param)) {
            $this->mysqlInstance->orderby($param['order']);
        }
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], (array_key_exists('offset', $param['limit'])? $param['limit']['offset']: NULL));
        }

        return $this->mysqlInstance->get()->all();
    }
    
    public function getTotalRowsTariffPlansList($where = array(), $like = array()) {
        $this->mysqlInstance->count()->from('tariff_plan')->where($where);
        if (!empty($like)) {
            $this->mysqlInstance->like($like, 'OR');
        }
        return $this->mysqlInstance->get()->counter();
    }
    
    public function deletePlanById($id) {
        return $this->mysqlInstance->delete('package_in_plan', array('id' => $id))->total_rows();
    }
    
    public function deleteTariffById($id) {
        return $this->mysqlInstance->delete('tariff_plan', array('id' => $id))->total_rows();
    }
    
    public function getOptionalForPlan($param) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('package_in_plan')->join('services_package', 'package_in_plan.package_id', 'services_package.id', 'LEFT');
        if (array_key_exists('where', $param)) {
            $this->mysqlInstance->where($param['where']);
        }
        if (array_key_exists('like', $param)) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (array_key_exists('order', $param)) {
            $this->mysqlInstance->orderby($param['order']);
        }
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], (array_key_exists('offset', $param['limit'])? $param['limit']['offset']: NULL));
        }

        return $this->mysqlInstance->get()->all();
    }
    
    public function updatePlan($param, $id){
        return $this->mysqlInstance->update('tariff_plan', $param, array('id'=>$id))->total_rows() || 1;
    }
    
    public function insertPlan($param){
        return $this->mysqlInstance->insert('tariff_plan', $param)->insert_id();
    }
    
    public function insertPackageInPlan($param){
        return $this->mysqlInstance->insert('package_in_plan', $param)->insert_id();
    }
    
    public function deletePackageInPlanById($id) {
        return $this->mysqlInstance->delete('package_in_plan', array('plan_id' => $id))->total_rows();
    }
    
    public function getUserDefaultPlan() {
        return $this->mysqlInstance->from('tariff_plan')->where(array('user_default' => 1))->get()->first('id');
    }

    public function getSubscribeLogList($param) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('package_subscribe_log as P_S_L')
            ->join('users as U', 'P_S_L.user_id', 'U.id', 'LEFT')
            ->join('administrators as A', 'P_S_L.initiator_id', 'A.id', 'LEFT')
            ->join('services_package as S_P', 'P_S_L.package_id', 'S_P.id', 'LEFT')
            ->where($param['where'])->like($param['like'], 'OR')->orderby($param['order']);
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], (array_key_exists('offset', $param['limit'])? $param['limit']['offset']: NULL));
        }

        return $this->mysqlInstance->get()->all();
    }

    public function getTotalRowsSubscribeLogList($where = array(), $like = array(), $user_id = FALSE) {
        if ($user_id !== FALSE && !array_key_exists('user_id', $where)) {
            $where['user_id'] = $user_id;
        }
        $this->mysqlInstance->count()->from('package_subscribe_log as P_S_L')
            ->join('services_package as S_P', 'P_S_L.package_id', 'S_P.id', 'LEFT')
            ->join('users as U', 'P_S_L.user_id', 'U.id', 'LEFT')
            ->join('administrators as A', 'P_S_L.initiator_id', 'A.id', 'LEFT')
            ->where($where);
        if (!empty($like)) {
            $this->mysqlInstance->like($like, 'OR');
        }
        return $this->mysqlInstance->get()->counter();
    }

    public function getUser($param) {
        return $this->mysqlInstance->from('users')->where($param, 'OR')->get()->first();
    }
}
