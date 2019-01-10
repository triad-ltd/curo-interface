<?php

namespace TriadLtd\CuroInterface;

class Events extends CuroInterface
{
    public function getEvents($parameters)
    {
        $out = $this->getClientEndpoint('/api/events', $parameters);

        return (object) $out;
    }

    public function book($parameters)
    {
        $this->postClientEndpoint('/api/events/' . $parameters['event_id'] . '/book', 'POST', $parameters);
    }
}
