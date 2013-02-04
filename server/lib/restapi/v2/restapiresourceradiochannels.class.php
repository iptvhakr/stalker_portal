<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiResourceRadioChannels extends RESTApiCollection
{
    protected $params_map = array("users" => "users.id");
    private   $user_id;
    protected $manager;
    protected $fields_map;

    public function __construct(array $nested_params, array $external_params){
        parent::__construct($nested_params, $external_params);

        $this->manager = \Radio::getInstance();

        if (!empty($this->nested_params['users.id'])){
            $user_id = $this->nested_params['users.id'];

            $user = \Stb::getById($user_id);

            if (empty($user)){
                throw new RESTNotFound("User not found");
            }

            $this->user_id = $user['id'];
        }
    }

    public function getCount(RESTApiRequest $request){
        return (int) $this->manager->getRawAllUserChannels($this->user_id)->count()->get()->counter();
    }

    public function get(RESTApiRequest $request){

        $channels = $this->manager->getRawAllUserChannels($this->user_id);

        if ($request->getLimit() !== null){
            $channels->limit($request->getLimit(), $request->getOffset());
        }

        return $this->filter($channels->get()->all());

    }

    public function filter($channels){

        $fields_map = $this->fields_map;

        $channels = array_map(function($channel) use ($fields_map){

            $new_channel = array();

            $new_channel['id']     = (int) $channel['id'];
            $new_channel['number'] = (int) $channel['number'];
            $new_channel['name']   = $channel['name'];

            if (preg_match("/(\S+:\/\/\S+)/", $channel['cmd'], $match)){
                $new_channel['url'] = $match[1];
            }

            return $new_channel;
        }, $channels);

        return $channels;
    }
}