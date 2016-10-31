<?php

use Stalker\Lib\Core\Config;

class Npm
{
    private $app_path;

    private static $instance = null;

    public static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->app_path = PROJECT_PATH.'/../../'.Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/');

        $registry = exec('npm get registry');

        if ($registry != Config::getSafe('npm_registry', 'http://registry.npmjs.org/')){
            system('npm cache clean 2>/dev/null');
            system('npm set registry '.escapeshellarg(Config::getSafe('npm_registry', 'http://registry.npmjs.org/')).' 2>/dev/null');
        }
    }

    public function install($package, $version = null){

        ob_start();

        if (!is_null($version)){
            $package .= '@'.$version;
        }

        system('cd '.$this->app_path.'; npm install '.escapeshellarg($package).' --production 2>/dev/null');

        $plain = trim(ob_get_contents());
        ob_clean();

        $this->relocatePackages();

        return !empty($plain);
    }

    public function update($package){

        ob_start();

        system('cd '.$this->app_path.'; npm update '.escapeshellarg($package).' --depth 0 2>/dev/null');

        $plain = trim(ob_get_contents());
        ob_clean();

        $this->relocatePackages();

        return !empty($plain);
    }

    public function info($package, $version = null){

        ob_start();

        if (!is_null($version)){
            $package .= '@'.$version;
        }

        system('npm view '.escapeshellarg($package).' --json 2>/dev/null');

        $plain = trim(ob_get_contents());
        ob_clean();

        $info = json_decode($plain, true);

        if (empty($info)){
            return false;
        }

        return $info;
    }

    private function relocatePackages($path = null, $package_order = null){

        if (is_null($path)){
            $path = $this->app_path;
        }

        if (is_null($package_order)){
            $package_order = array();
        }

        $packages_path = realpath($path.'/node_modules');

        if (!$packages_path){
            return;
        }

        $scanned_directory = array_diff(scandir($packages_path), array('..', '.'));

        $scanned_directory = array_merge($package_order, array_diff($scanned_directory, $package_order));

        foreach ($scanned_directory as $dir) {

            $full_path = $packages_path.'/'.$dir;
            if (is_dir($full_path)){

                if(is_readable($full_path.'/package.json')){
                    $info = file_get_contents($full_path.'/package.json');
                    $info = json_decode($info, true);
                    if (empty($info)){
                        continue;
                    }
                }

                if (!isset($info['version'])){
                    continue;
                }

                try{
                    $version = new SemVer($info['version']);
                    $ver = $version->getVersion();
                }catch (SemVerException $e){
                    throw new NodeException($e->getMessage());
                }

                $package_order = isset($info['dependencies']) ? array_keys($info['dependencies']) : null;

                $this->relocatePackages($full_path, $package_order);

                umask(0);

                if (isset($info['config']['type']) && $info['config']['type'] == 'plugin'){

                    $plugins_path = $this->app_path.'/plugins';

                    if (!is_dir($plugins_path)){
                        mkdir($plugins_path, 0777, true);
                    }

                    $target_path = $plugins_path.'/'.$dir.'/'.$ver;
                }else{
                    $target_path = $this->app_path.'/'.$dir.'/'.$ver;
                }

                if (!is_dir($target_path)){
                    mkdir($target_path, 0777, true);
                    rename($full_path, $target_path);
                }else{
                    self::delTree($full_path);
                }

                SmartLauncherAppsManager::getInstance()->addApplication($dir, true, false, $ver);
            }
        }
    }

    private static function delTree($dir) {
        if (!is_dir($dir)){
            return false;
        }
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}

class NodeException extends Exception{}