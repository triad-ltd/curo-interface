<?php

namespace TriadLtd\CuroInterface;

class Events extends CuroInterface
{
    public function getEvents($parameters)
    {
        $out = $this->getClientEndpoint('/api/v1/events', $parameters);

        return (object) $out;
    }

    public function book($parameters)
    {
        $this->postClientEndpoint('/api/v1/events/' . $parameters['event_id'] . '/book', 'POST', $parameters);
    }

    public function store(array $parameters = [])
    {
        $this->postUserEndpoint('/api/v1/events', 'POST', $parameters);
        $this->clearCache('/api/v1/events');
    }

    public function update(string $event_uuid, array $parameters = [])
    {
        $out = $this->postClientEndpoint('/api/v1/events/' . $event_uuid, 'PUT', $parameters);
        $this->clearCache('/api/v1/events');
        $this->clearCache('/api/v1/events/' . $event_uuid);
        return (object) $out;
    }

    public function delete(int $event_id)
    {
        $out = $this->postUserEndpoint('/api/v1/events/' . $event_id, 'DELETE');
        $this->clearCache('/api/v1/events');
        $this->clearCache('/api/v1/events/' . $event_id);
    }
}
