<?php

namespace App\WS;

use App\ChatMessage;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class ChatSocket implements MessageComponentInterface
{
    /** @var \SplObjectStorage */
    protected $clients;

    /** @var ChatMessage */
    protected $chatMessageAr;

    /**
     * ChatSocket constructor. DI
     * @param ChatMessage $chatMessageAr
     */
    public function __construct(ChatMessage $chatMessageAr)
    {
        $this->clients = new \SplObjectStorage();
        $this->chatMessageAr = $chatMessageAr;
    }

    /**
     * {@inheritdoc}
     */
    function onOpen(ConnectionInterface $conn)
    {
        $this
            ->clients
            ->attach($conn);
        echo "New connection " . $conn->resourceId . "\n";

        $history = $this->chatMessageAr
            ->newQuery()
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->all();

        foreach ($history as $message) {
            /** @var ChatMessage $message */
            $prefix = '[' . $conn->resourceId . ']: ';

            if($conn->resourceId == $message->res_id) {
                $prefix = '[me]: ';
            }

            $conn->send($prefix . $message->msg);
        }
    }

    /**
     * {@inheritdoc}
     */
    function onClose(ConnectionInterface $conn)
    {
        $this
            ->clients
            ->detach($conn);

        foreach ($this->clients as $currClient) {
            $currClient->send('[' . $conn->resourceId . '] > Has left the chat');
        }
    }

    /**
     * {@inheritdoc}
     */
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Error " . $e->getMessage() . "\n";
        $conn->close();
    }

    /**
     * {@inheritdoc}
     */
    function onMessage(ConnectionInterface $from, $msg)
    {
        $this
            ->chatMessageAr
            ->newInstance([
                'res_id' => (int)$from->resourceId,
                'msg' => $msg,
            ])
            ->save();
        echo '[' . $from->resourceId . ']: ' . $msg . "\n";

        foreach ($this->clients as $currClient) {
            if ($from === $currClient) {
                continue;
            }

            $currClient->send('[' . $from->resourceId . ']: ' . $msg);
        }
    }
}