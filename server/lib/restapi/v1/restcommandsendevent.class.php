<?php

namespace Stalker\Lib\RESTAPI\v1;

class RESTCommandSendEvent extends RESTCommand
{
    public function create(RESTRequest $request){

        $stb_list = $request->getConvertedIdentifiers();

        $identifiers = $request->getIdentifiers();

        if (empty($stb_list) && !empty($identifiers)){
            throw new RESTCommandException('STB not found');
        }

        $event = new \SysEvent();

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

                if ($request->getData('auto_hide_timeout')){
                    $event->setAutoHideTimeout($request->getData('auto_hide_timeout'));
                }
                break;
            case 'send_msg_with_video':
                $event->sendMsgWithVideo($request->getData('msg'), $request->getData('video'));
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
            case 'play_radio_channel':
                $event->sendPlayRadioChannel($request->getData('channel'));
                break;
            case 'update_image':
                $event->sendUpdateImage();
                break;
            case 'cut_off':
                $event->sendCutOff();
                break;
            case 'show_menu':
                $event->sendShowMenu();
                break;
            default:
                return false;
        }

        return true;
    }
}
