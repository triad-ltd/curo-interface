<?php

namespace TriadLtd\CuroInterface;

class Users extends CuroInterface
{
    public function me()
    {
        $out = $this->getUserEndpoint('/api/users/me');
        return (object) $out;
    }

    public function users(array $parameters = [])
    {
        $out = $this->getClientEndpoint('/api/users', $parameters);
        return (object) $out;
    }

    public function store(array $parameters = [])
    {
        $this->postUserEndpoint('/api/users', 'POST', $parameters);
        $this->clearCache('/api/users');
    }
}
