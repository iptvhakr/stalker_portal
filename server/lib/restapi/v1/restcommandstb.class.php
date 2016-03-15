<?php

namespace Stalker\Lib\RESTAPI\v1;

use Stalker\Lib\Core\Stb;
use Stalker\Lib\Core\Config;
use Stalker\Lib\Core\Middleware;

class RESTCommandStb extends RESTCommand
{
    private $manager;
    private $allowed_fields;

    public function __construct(){
        $this->manager = Stb::getInstance();
        $this->allowed_fields = array_fill_keys(array('mac', 'ls', 'login', 'status', 'online', 'additional_services_on', 'ip', 'version', 'expire_billing_date', 'account_balance', 'last_active'), true);
    }

    public function get(RESTRequest $request){

        $stb_list = $this->manager->getByUids($request->getConvertedIdentifiers());

        return $this->formatList($stb_list);
    }

    public function update(RESTRequest $request){

        $put = $request->getPut();

        if (empty($put)){
            throw new RESTCommandException('HTTP PUT data is empty');
        }

        $allowed_to_update_fields = array_fill_keys(array('status', 'additional_services_on', 'ls', 'reboot', 'end_date'), true);

        $data = array_intersect_key($put, $allowed_to_update_fields);

        if (array_key_exists('status', $data)){
            $data['status'] = intval(!$data['status']);
        }

        if (isset($data['end_date'])){
            $data['expire_billing_date'] = $data['end_date'];
            unset($data['end_date']);
        }

        if (empty($data)){
            throw new RESTCommandException('Update data is empty');
        }

        if (count($request->getIdentifiers()) == 0 && !Config::getSafe('allow_multiple_stb_update', false)){
            throw new RESTCommandException('Identifier required');
        }

        $stb_list = $this->manager->updateByUids($request->getConvertedIdentifiers(), $data);

        if (empty($stb_list)){
            return false;
        }

        return $this->formatList($stb_list);
    }

    public function delete(RESTRequest $request){

        if (count($request->getIdentifiers()) == 0){
            throw new RESTCommandException('Identifier required');
        }

        $stb_list = $request->getConvertedIdentifiers();

        if (count($stb_list) == 0){
            throw new RESTCommandException('STB not found');
        }

        return $this->manager->deleteById($stb_list);
    }

    public function create(RESTRequest $request){

        $data = $request->getData();

        if (empty($data)){
            throw new RESTCommandException('HTTP POST data is empty');
        }

        $allowed_to_update_fields = array_fill_keys(array('mac', 'login', 'password', 'status', 'additional_services_on', 'ls', 'end_date', 'account_balance'), true);

        $data = array_intersect_key($data, $allowed_to_update_fields);

        if (empty($data)){
            throw new RESTCommandException('Insert data is empty');
        }

        if (isset($data['end_date'])){
            $data['expire_billing_date'] = $data['end_date'];
            unset($data['end_date']);
        }

        if (!empty($data['mac'])){
            $mac = Middleware::normalizeMac($data['mac']);

            if (!$mac){
                throw new RESTCommandException('Not valid mac address');
            }

            $data['mac'] = $mac;
        }

        if (empty($data['mac']) && (empty($data['login']) || empty($data['password']))){
            throw new RESTCommandException('Login and password required');
        }

        try{
            $uid = Stb::create($data);
        }catch(\Exception $e){
            throw new RESTCommandException($e->getMessage());
        }
        
        $stb_list = $this->manager->getByUids(array($uid));

        $stb_list = $this->formatList($stb_list);

        if (count($stb_list) == 1){
            return $stb_list[0];
        }

        return $stb_list;
    }

    private function formatList($list){

        $allowed_fields = $this->allowed_fields;

        $enable_internal_billing = Config::getSafe('enable_internal_billing', false);

        $list = array_map(function($item) use ($allowed_fields, $enable_internal_billing){

            $item = array_intersect_key($item, $allowed_fields);

            $item['status'] = intval(!$item['status']);

            if ($enable_internal_billing){
                $item['end_date'] = $item['expire_billing_date'];
            }

            unset($item['expire_billing_date']);

            return $item;
        },
        $list
        );

        return $list;
    }
}

?>