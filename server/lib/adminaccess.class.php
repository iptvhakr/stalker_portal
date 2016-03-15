<?php

use Stalker\Lib\Core\Mysql;

class AdminAccess
{

    const ACCESS_VIEW           = 'view';
    const ACCESS_CREATE         = 'create';
    const ACCESS_DELETE         = 'delete';
    const ACCESS_EDIT           = 'edit';
    const ACCESS_PAGE_ACTION    = 'page_action';
    const ACCESS_CONTEXT_ACTION = 'context_action';

    private $admin;

    public function __construct(Admin $admin) {
        $this->admin = $admin;
    }

    public function check($page, $action = 'view') {

        if ($this->admin->getLogin() == 'admin') {
            return true;
        }

        return (bool)Mysql::getInstance()
            ->from('acl')
            ->where(array(
                'gid'            => $this->admin->getGID(),
                'page'           => $page,
                'acl.' . $action => 1
            ))
            ->get()
            ->first();
    }

    public static function convertPostParamsToAccessMap($post_data){

        $fields = array('view', 'create', 'edit', 'delete', 'page_action', 'context_action');

        $map = array();

        foreach ($fields as $field){

            if (!isset($post_data[$field])){
                continue;
            }

            foreach ($post_data[$field] as $page => $val){

                if (!isset($map[$page])){
                    $map[$page] = array_fill_keys($fields, 0);
                    $map[$page]['page'] = $page;
                }

                $map[$page][$field] = $val;
            }
        }

        return array_values($map);
    }
}