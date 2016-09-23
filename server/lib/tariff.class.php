<?php

use Stalker\Lib\Core\Mysql;

class Tariff
{

    public static function getDetailedPlanInfo($plan_id = null){

        if (!empty($plan_id)){
            $info = self::getPlanById($plan_id);

            if (!empty($info)){
                $info['packages'] = Tariff::getPackagesForTariffPlan($info['id']);
            }

        }else{
            $info = self::getAllPlans();

            $info = array_map(function($plan){

                $plan['packages'] = Tariff::getPackagesForTariffPlan($plan['id']);

                return $plan;
            }, $info);
        }

        return $info;
    }

    public static function getDetailedPackageInfo($package_id = null){

        if (!empty($package_id)){
            $info = self::getPackageById($package_id);

            if (!empty($info)){
                $info['services'] = Tariff::getServicesForPackage($info['id']);
            }

        }else{
            $info = self::getAllPackages();

            $info = array_map(function($package){

                $package['services'] = Tariff::getServicesForPackage($package['id']);

                return $package;
            }, $info);
        }

        return $info;
    }

    public static function getPlanById($plan_id){
        return Mysql::getInstance()->from('tariff_plan')->where(array('id' => $plan_id))->get()->first();
    }

    public static function getPackageById($package_id){
        return Mysql::getInstance()
            ->from('services_package')
            ->where(array(
                'id' => $package_id
            ))
            ->get()
            ->first();
    }

    public static function getAllPackages(){
        return Mysql::getInstance()
            ->from('services_package')
            ->get()
            ->all();
    }

    public static function getAllPlans(){

        return Mysql::getInstance()
            ->from('tariff_plan')
            ->get()
            ->all();
    }

    public static function getPackagesForTariffPlan($plan_id){

        return Mysql::getInstance()
            ->select('services_package.*, package_in_plan.optional')
            ->from('services_package')
            ->join('package_in_plan', 'package_in_plan.package_id', 'services_package.id', 'INNER')
            ->where(array(
                'plan_id' => $plan_id
            ))
            ->get()
            ->all();
    }

    public static function getServicesForPackage($package_id){

        $package = self::getPackageById($package_id);

        if ($package['all_services'] == 1){
            return 'all';
        }

        $service_ids = Mysql::getInstance()
            ->from('service_in_package')
            ->where(array('package_id' => $package_id))
            ->get()
            ->all('service_id');

        $services = Mysql::getInstance();

        if ($package['type'] == 'tv'){

            $services = $services->select('id, name')->from('itv')->in('id', $service_ids)->orderby('name')->get()->all();

        }elseif($package['type'] == 'radio'){

            $services = $services->select('id, name')->from('radio')->in('id', $service_ids)->orderby('name')->get()->all();

        }elseif($package['type'] == 'video'){

            $services = $services->select('id, name')->from('video')->in('id', $service_ids)->orderby('name')
                ->get()->all();

        }else{
            $services = array_unique($service_ids);
        }

        return $services;
    }

}