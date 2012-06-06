<?php

class Account
{
    public function subscribeToPackage(){
        $package_id = (int) $_REQUEST['package_id'];

        $user = User::getInstance(Stb::getInstance()->id);

        return $user->subscribeToPackage($package_id);
    }

    public function unsubscribeFromPackage(){

        $package_id = (int) $_REQUEST['package_id'];

        $user = User::getInstance(Stb::getInstance()->id);

        return $user->unsubscribeFromPackage($package_id);
    }
}