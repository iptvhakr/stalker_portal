<?php

namespace Stalker\Lib\RESTAPI\v1;

class RESTCommandServicesPackage extends RESTCommand
{

    public function get(RESTRequest $request){

        $identifiers = $request->getIdentifiers();

        if (!empty($identifiers[0])){
            $package_id = (int) $identifiers[0];
        }else{
            $package_id = null;
        }

        $result = \Tariff::getDetailedPackageInfo($package_id);

        return $result;
    }

}