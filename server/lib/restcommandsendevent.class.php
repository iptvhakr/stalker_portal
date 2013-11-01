<?php

class RESTCommandSendEvent extends RESTCommand
{
    public function create(RESTRequest $request){

        $stb_list = $request->getConvertedIdentifiers();

        $identifiers = $request->getIdentifiers();

        if (empty($stb_list) && !empty($identifiers)){
            throw new RESTCommandException('STB not found');
        }

        $event = new SysEvent();

        if (empty($identifiers)){
            $event->setUserListByMac('all');
        }else{
            $event->setUserListById($stb_list);
        }

        if ($request->getData('ttl')){
            $event->setTtl($request->getData('ttl'));
        }

        switch ($request->getData('event')) {
            case 'send_msg':
                if ($request->getData('need_reboot')){
                    $event->sendMsgAndReboot($request->getData('msg'));
                }else{
                    $event->sendMsg($request->getData('msg'));
                }
                break;
            case 'reboot':
                $event->sendReboot();
                break;
            case 'reload_portal':
                $event->sendReloadPortal();
                break;
            case 'update_channels':
                $event->sendUpdateChannels();
                break;
            case 'play_channel':
                $event->sendPlayChannel($request->getData('channel'));
                break;
            case 'update_image':
                $event->sendUpdateImage();
                break;
            case 'cut_off':
                $event->sendCutOff();
                break;
            default:
                return false;
        }

        return true;
    }
}
