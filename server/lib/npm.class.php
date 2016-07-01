<?php

use Stalker\Lib\Core\Config;

class Npm
{
    private $app_path;

    public function __construct() {
        $this->app_path = PROJECT_PATH.'/../../'.Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/');
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

    private function relocatePackages($path = null){

        if (is_null($path)){
            $path = $this->app_path;
        }

        $packages_path = realpath($path.'/node_modules');

        if (!$packages_path){
            return;
        }

        $scanned_directory = array_diff(scandir($packages_path), array('..', '.'));

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

                $this->relocatePackages($full_path);

                $target_path = $this->app_path.'/'.$dir.'/'.$ver;

                umask(0);
                if (!is_dir($target_path)){
                    mkdir($target_path, 0777, true);
                }

                rename($full_path, $target_path);

                $app_manager = new SmartLauncherAppsManager();
                $app_manager->addApplication($dir);
            }
        }
    }
}

class NodeException extends Exception{}