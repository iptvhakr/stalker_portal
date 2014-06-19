<?php

class RESTCommandStbAuth extends RESTCommand
{
    public function update(RESTRequest $request){

        $stb_list = $request->getConvertedIdentifiers();

        if (empty($stb_list)){
            throw new RESTCommandException('Empty stb list');
        }

        foreach ($stb_list as $uid){
            Mysql::getInstance()->update('users',
                array('access_token' => strtoupper(md5(mktime(1).uniqid()))),
                array('id' => $uid)
            );
        }

        return true;
    }
}
