<?php

$entryData = array(
    'category' => 'myCategory',
    'title'    => 'the title',
    'article'  => 'the article',
    'when'     => time()
);

$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
$socket->connect("tcp://localhost:5555");

$socket->send(json_encode($entryData));

echo phpinfo();