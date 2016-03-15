<?php

namespace Stalker\Lib\RESTAPI\v1;

class RESTCommandStbMsg extends RESTCommand
{
    public function create(RESTRequest $request){

        $stb_list = $request->getConvertedIdentifiers();

        if (empty($stb_list)){
            throw new RESTCommandException('Empty stb list');
        }

        $msg = $request->getData("msg");

        if (empty($msg)){
            throw new RESTCommandException('Empty msg');
        }

        $event = new \SysEvent();

        $ttl = (int) $request->getData("ttl");

        if (!empty($ttl)){
            $event->setTtl($ttl);
        }

        $auto_hide_timeout = (int) $request->getData('auto_hide_timeout');
        if ($auto_hide_timeout){
            $event->setAutoHideTimeout($auto_hide_timeout);
        }

        $event->setUserListById($stb_list);

        $event->sendMsg($msg);

        return true;
    }
}
?>
