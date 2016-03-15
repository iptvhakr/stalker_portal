<?php

namespace Stalker\Lib\RESTAPI\v1;

class RESTCommandTariffs extends RESTCommand
{
    public function get(RESTRequest $request){

        $identifiers = $request->getIdentifiers();

        if (!empty($identifiers[0])){
            $plan_id = (int) $identifiers[0];
        }else{
            $plan_id = null;
        }

        $result = \Tariff::getDetailedPlanInfo($plan_id);

        return $result;
    }
}