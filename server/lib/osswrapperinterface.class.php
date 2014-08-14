<?php

interface OssWrapperInterface
{
    public function getUserInfo(User $user);

    public function registerSTB($mac, $serial_number, $model);

    public function getPackagePrice($ext_package_id, $package_id);

    public function subscribeToPackage($ext_package_id);

    public function unsubscribeFromPackage($ext_package_id);
}

class OssException extends Exception{};
class OssFault extends OssException{}; // Server error
class OssError extends OssException{}; // Code != 0
class OssDeny extends OssException{}; // Code != 0