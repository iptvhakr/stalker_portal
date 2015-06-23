<?php

class RESTCommandMonitoringLinks extends RESTCommand
{
    private $manager;
    private $allowed_fields;

    public function __construct(){
        $this->manager = Itv::getInstance();
    }

    public function get(RESTRequest $request){

        if (empty($request) || strpos($request->getAccept(), 'text/channel-monitoring-id-url') === false){
            throw new RESTCommandException('Unsupported Accept header, use text/channel-monitoring-id-url');
        }

        $this->setManager($request);

        return $this->manager->getLinksForMonitoring(@$_GET['status']);
    }

    public function update(RESTRequest $request){

        $this->setManager($request);
        $put = $request->getPut();

        if (empty($put)){
            throw new RESTCommandException('HTTP PUT data is empty');
        }

        $allowed_to_update_fields = array_fill_keys(array('status', 'link_id'), true);

        $data = array_intersect_key($put, $allowed_to_update_fields);

        if (empty($data)){
            throw new RESTCommandException('Update data is empty');
        }

        $ids = $request->getIdentifiers();

        if (empty($ids)){
            throw new RESTCommandException('Empty link id');
        }

        if ($ids[0] == 'radio' || $ids[0] == 'itv') {
            if (array_key_exists('link_id', $data)) {
                $link_id = $data['link_id'];
            } else {
                $link_id = FALSE;
            }
        } else {
            $link_id = $ids[0];
        }

        $manager_class = get_class($this->manager);

        return $manager_class::setChannelLinkStatus($link_id, (int) $data['status']);

        //return Mysql::getInstance()->update('itv', $data, array('id' => $channel_id));
    }

    private function setManager(RESTRequest $request){
        $identifiers = $request->getIdentifiers();
        $base_class = ucfirst(!empty($identifiers[0]) && !is_numeric($identifiers[0]) ? $identifiers[0]: 'itv');
        if (class_exists($base_class)) {
            $this->manager = $base_class::getInstance();
        }
    }
}

?>