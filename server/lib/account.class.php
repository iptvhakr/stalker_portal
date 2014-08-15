<?php

class Account implements \Stalker\Lib\StbApi\Account
{
    public function subscribeToPackage(){
        $package_id = (int) $_REQUEST['package_id'];

        $user = User::getInstance(Stb::getInstance()->id);

        $response = array();

        try{
            $response['result'] = $user->subscribeToPackage($package_id);
        }catch (OssDeny $e){
            $response['message'] = $e->getMessage();
            Stb::logOssError($e);
        }catch (OssException $e){
            $response['message'] = _('This operation is temporarily unavailable.');
            Stb::logOssError($e);
        }

        return $response;
    }

    public function unsubscribeFromPackage(){

        $package_id = (int) $_REQUEST['package_id'];

        $user = User::getInstance(Stb::getInstance()->id);

        $response = array();

        try{
            $response['result'] = $user->unsubscribeFromPackage($package_id);
        }catch (OssDeny $e){
            $response['message'] = $e->getMessage();
            Stb::logOssError($e);
        }catch (OssException $e){
            $response['message'] = _('This operation is temporarily unavailable.');
            Stb::logOssError($e);
        }

        return $response;
    }

    public function checkPrice(){

        $package_id = (int) $_REQUEST['package_id'];

        $user = User::getInstance(Stb::getInstance()->id);

        $response = array();

        try{
            $response['result'] = $user->getPriceForPackage($package_id);
        }catch (OssDeny $e){
            $response['message'] = $e->getMessage();
            Stb::logOssError($e);
        }catch (OssException $e){
            $response['message'] = _('This operation is temporarily unavailable.');
            Stb::logOssError($e);
        }

        return $response;
    }

    public function checkVideoPrice(){

        $video_id = (int) $_REQUEST['video_id'];

        $user = User::getInstance(Stb::getInstance()->id);

        $response = array();

        try{

            $package = $user->getPackageByVideoId($video_id);

            if (empty($package)){
                throw new Exception(_('Server error'));
            }

            $response['result'] = $user->getPriceForPackage($package['id']);
            $response['rent_duration'] = $package['rent_duration'];
            $response['package_id']    = $package['id'];
        }catch (OssDeny $e){
            $response['message'] = $e->getMessage();
            Stb::logOssError($e);
        }catch (OssException $e){
            $response['message'] = _('This operation is temporarily unavailable.');
            Stb::logOssError($e);
        }catch (Exception $e){
            $response['message'] = $e->getMessage();
            Stb::logOssError($e);
        }

        return $response;
    }

    public function rentVideo(){

        $video_id = (int) $_REQUEST['video_id'];
        $price    = $_REQUEST['price'];

        $user = User::getInstance(Stb::getInstance()->id);

        $response = array();

        try{

            $package = $user->getPackageByVideoId($video_id);

            if (empty($package)){
                throw new Exception(_('Server error'));
            }

            if ($price === '0'){
                $oss_result = true;
            }else{
                $oss_result = $user->subscribeToPackage($package['id']);
            }

            $response['result'] = $oss_result;
            $response['rent_duration'] = $package['rent_duration'];
            $response['package_id']    = $package['id'];

            $rent_session_id = $user->rentVideo($video_id, $price);

            $response['rent_info'] = Mysql::getInstance()
                ->from('video_rent')
                ->where(array('id' => $rent_session_id))
                ->get()
                ->first();

            $response['rent_info']['expires_in'] = User::humanDateDiff($response['rent_info']['rent_end_date'], $response['rent_info']['rent_date']);

        }catch (OssDeny $e){
            $response['message'] = $e->getMessage();
            Stb::logOssError($e);
        }catch (OssException $e){
            $response['message'] = _('This operation is temporarily unavailable.');
            Stb::logOssError($e);
        }catch (Exception $e){
            $response['message'] = $e->getMessage();
            Stb::logOssError($e);
        }

        return $response;
    }
}