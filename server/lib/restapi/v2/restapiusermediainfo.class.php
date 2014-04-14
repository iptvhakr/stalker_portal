<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiUserMediaInfo extends RESTApiController
{

    protected $name = 'media-info';
    protected $types_map = array(
        'tv-channel' => array(
            'code'   => 1,
            'target' => 'itv',
            'title_field' => 'name'
        ),
        'video'      => array(
            'code'   => 2,
            'target' => 'video',
            'title_field' => 'name'
        ),
        'karaoke'    => array(
            'code'   => 3,
            'target' => 'karaoke',
            'title_field' => 'name'
        ),
    );

    public function __construct(){}

    public function create(RESTApiRequest $request, $parent_id){

        $type     = $request->getData('type');
        $media_id = (int) $request->getData('media_id');

        if (empty($type)){
            throw new RESTBadRequest("Type is empty");
        }

        if (empty($media_id)){
            throw new RESTBadRequest("Media ID is empty");
        }

        if (empty($this->types_map[$type])){
            throw new RESTBadRequest("Type is not supported");
        }

        $media = \Mysql::getInstance()->from($this->types_map[$type]['target'])
            ->where(array('id' => $media_id))
            ->get()->first();

        if (empty($media)){
            throw new RESTNotFound("Media not found");
        }

        return \Mysql::getInstance()->update('users',
            array(
                 'now_playing_type'    => $this->types_map[$type]['code'],
                 'now_playing_content' => $media[$this->types_map[$type]['title_field']],
                 'now_playing_start'   => 'NOW()',
                 'last_active'         => 'NOW()',
            ),
            array('id' => $parent_id)
        )->result();
    }

    public function delete(RESTApiRequest $request, $parent_id){

        //todo: save storage name for video and karaoke?
        //todo: load balancing

        return \Mysql::getInstance()->update('users',
            array(
                 'now_playing_type'    => '',
                 'now_playing_content' => '',
                 'last_active'         => 'NOW()',
            ),
            array('id' => $parent_id)
        )->result();
    }
}