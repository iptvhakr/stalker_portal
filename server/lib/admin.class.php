<?php

class Admin
{
    private $profile;
    /** @var Admin */
    private static $instance = null;

    public static function getInstance(){

        if (self::$instance == null){
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct() {

        $sid = session_id();

        if (empty($sid)){
            session_start();
        }

        if (!empty($_SESSION['uid'])){
            $this->profile = Mysql::getInstance()
                ->from('administrators')
                ->where(array(
                    'id' => $_SESSION['uid']
                ))
                ->get()
                ->first();
        }
    }

    public function getId(){
        return $this->profile['id'];
    }

    public function getGID(){
        return $this->profile['gid'];
    }

    public function getLogin(){
        return $this->profile['login'];
    }

    public static function checkAuthorization($login, $pass) {

        $admin = Mysql::getInstance()
            ->from('administrators')
            ->where(array(
                'login' => $login
            ))
            ->get()
            ->first();

        if (empty($admin)) {
            return false;
        }

        if ($admin['pass'] == md5($pass)) {

            if (self::$instance == null){
                self::getInstance();
            }

            $_SESSION['uid']    = $admin['id'];
            $_SESSION['login']  = $admin['login'];
            $_SESSION['pass']   = $admin['pass'];

            return true;
        }

        return false;
    }

    public static function checkAuth(){

        $admin = self::getInstance();

        if (!$admin->isAuthorized()){
            header("Location: login.php");
            exit();
        }
    }

    public function isAuthorized() {

        if (empty($_SESSION['login']) || empty($_SESSION['pass'])){
            return false;
        }

        $admin = Mysql::getInstance()
            ->from('administrators')
            ->where(array(
                'login' => $_SESSION['login']
            ))
            ->get()
            ->first();

        if (empty($admin)){
            return false;
        }

        $is_authorized = $admin['pass'] == $_SESSION['pass'];

        if ($is_authorized){
            $this->profile = $admin;
        }

        return $is_authorized;
    }

    private static function getCurrentPage(){

        $page = '';

        if (preg_match("/\/([^\/]+)\./", $_SERVER['PHP_SELF'], $match)){
            $page = $match[1];
        }

        return $page;
    }

    public static function isSuperUser(){

        if (self::$instance == null){
            self::getInstance();
        }

        return self::$instance->getLogin() == 'admin';
    }

    private static function isAllowed($action, $page = null){

        if (self::$instance == null){
            self::getInstance();
        }

        if ($page === null){
            $page = self::getCurrentPage();
        }

        $access = new AdminAccess(self::$instance);
        return $access->check($page, $action);
    }

    public static function isActionAllowed($page = null){
        return self::isAllowed(AdminAccess::ACCESS_CONTEXT_ACTION, $page);
    }

    public static function isPageActionAllowed($page = null){
        return self::isAllowed(AdminAccess::ACCESS_PAGE_ACTION, $page);
    }

    public static function isCreateAllowed($page = null){
        return self::isAllowed(AdminAccess::ACCESS_CREATE, $page);
    }

    public static function isEditAllowed($page = null){
        return self::isAllowed(AdminAccess::ACCESS_EDIT, $page);
    }

    public static function isAccessAllowed($page = null){
        return self::isAllowed(AdminAccess::ACCESS_VIEW, $page);
    }

    public static function checkAccess($action = 'view', $page = null){

        if ($page === null){
            $page = self::getCurrentPage();
        }

        if (!self::isAllowed($action, $page)){
            echo sprintf(_('Action "%s" denied for page "%s"'), $action, $page);
            exit;
        }
    }
}