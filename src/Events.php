<?php

namespace TriadLtd\CuroInterface;

class Events extends CuroInterface
{
    public function getEvents($parameters)
    {
        $out = $this->getClientEndpoint('/api/events', $parameters);
        return (object) $out;
    }
}
