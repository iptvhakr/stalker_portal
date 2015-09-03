<?php

class AppsManager
{

    public function getList(){

        $db_apps = Mysql::getInstance()->from('apps')->orderby('id')->get()->all();

        $apps = array_map(function($app){

            $repo = new GitHub($app['url']);

            $info = $repo->getFileContent('package.json');
            $app['name']              = isset($info['name']) ? $info['name'] : '';
            $app['alias']             = AppsManager::safeFilename($info['name']);
            $app['available_version'] = isset($info['version']) ? $info['version'] : '';
            $app['description']       = isset($info['description']) ? $info['description'] : '';

            $app['installed'] = is_dir(realpath(PROJECT_PATH.'/../../'
                .Config::getSafe('apps_path', 'stalker_apps/')
                .$app['alias']
                .'/'.$app['current_version']));

            return $app;
        }, $db_apps);

        return $apps;
    }

    public function getAppInfo($app_id){

        $app = Mysql::getInstance()->from('apps')->where(array('id' => $app_id))->get()->first();

        if (empty($app)){
            return false;
        }

        $repo = new GitHub($app['url']);

        return $repo->getFileContent('package.json');
    }

    public function installApp($app_id){

        $app = Mysql::getInstance()->from('apps')->where(array('id' => $app_id))->get()->first();

        if (empty($app)){
            return false;
        }

        $repo = new GitHub($app['url']);
        $versions = $repo->getReleases(1);

        if (count($versions) == 0){
            return false;
        }

        $latest_release = $versions[0];

        $tmp_file = tempnam('/tmp', 'app');

        file_put_contents($tmp_file, fopen($latest_release['tarball_url'], 'r'));

        $archive = new PharData($tmp_file);

        $path = realpath(PROJECT_PATH.'/../../'.Config::getSafe('apps_path', 'stalker_apps/').self::safeFilename($app['name']).'/'.$latest_release['name']);

        umask(0);
        mkdir($path, 0755, true);

        $result = $archive->extractTo($path);

        unlink($tmp_file);

        if ($result){
            Mysql::getInstance()->update('apps', array('current_version' => $latest_release['name']), array('id' => $app_id));
        }

        return $result;
    }

    public function updateApp($app_id, $version = null, $tarball_url = null){

        $app = Mysql::getInstance()->from('apps')->where(array('id' => $app_id))->get()->first();

        if (empty($app)){
            return false;
        }

        if ($version === null){
            return $this->installApp($app_id);
        }

        $tmp_file = tempnam('/tmp', 'app');

        if ($tarball_url === null){
            $repo = new GitHub($app['url']);
            $tarball_url = 'https://api.github.com/repos/'.$repo->getOwner().'/'.$repo->getRepository().'/tarball/'.$version;
        }

        file_put_contents($tmp_file, fopen($tarball_url, 'r'));

        $archive = new PharData($tmp_file);

        $path = realpath(PROJECT_PATH.'/../../'.Config::getSafe('apps_path', 'stalker_apps/').self::safeFilename($app['name']).'/'.$version);

        umask(0);
        mkdir($path, 0755, true);

        $result = $archive->extractTo($path);

        unlink($tmp_file);

        if ($result){
            Mysql::getInstance()->update('apps', array('current_version' => $version), array('id' => $app_id));
        }

        return $result;
    }

    public function deleteApp($app_id, $version = null){

        $app = Mysql::getInstance()->from('apps')->where(array('id' => $app_id))->get()->first();

        if ($version === null){
            $version = $app['current_version'];
        }

        $path = realpath(PROJECT_PATH.'/../../'.Config::getSafe('apps_path', 'stalker_apps/').self::safeFilename($app['name']).'/'.$version);

        if (is_dir($path)){
            self::delTree($path);
        }

        return false;
    }

    public static function safeFilename($filename){
        $except = array('\\', '/', ':', '*', '?', '"', '<', '>', '|');
        return strtolower(str_replace($except, '', $filename));
    }

    private static function delTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}
