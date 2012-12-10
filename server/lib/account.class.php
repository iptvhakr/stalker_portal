<?php

class Account
{
    public function subscribeToPackage(){
        $package_id = (int) $_REQUEST['package_id'];

        $user = User::getInstance(Stb::getInstance()->id);

        $response = array();

        try{
            $response['result'] = $user->subscribeToPackage($package_id);
        }catch (OssDeny $e){
            $response['message'] = $e->getMessage();
        }catch (OssException $e){
            $response['message'] = _('This operation is temporarily unavailable.');
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
        }catch (OssException $e){
            $response['message'] = _('This operation is temporarily unavailable.');
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
        }catch (OssException $e){
            $response['message'] = _('This operation is temporarily unavailable.');
        }

        return $response;
    }
}