<?php

namespace Stalker;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Pusher implements WampServerInterface {

    protected $subscribedTopics = array();

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        $this->subscribedTopics[$topic->getId()] = $topic;
    }

    /**
     * @param string $message JSON'ified string we'll receive from ZeroMQ
     */
    public function onMessage($message) {
        $messageData = json_decode($message, true);

        // If the lookup topic object isn't set there is no one to publish to
        if (!array_key_exists($messageData['topic'], $this->subscribedTopics)) {
            return;
        }

        $topic = $this->subscribedTopics[$messageData['topic']];

        // re-send the data to all the clients subscribed to that category
        $topic->broadcast($messageData);
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
    }

    public function onOpen(ConnectionInterface $conn) {
    }
    public function onClose(ConnectionInterface $conn) {
    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->close();
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
}