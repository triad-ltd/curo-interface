<?php

namespace TriadLtd\CuroInterface;

class Users extends CuroInterface
{
    public function delete(int $user_id)
    {
        $out = $this->postUserEndpoint('/api/users/' . $user_id, 'DELETE');
        $this->clearCache('/api/users');
        $this->clearCache('/api/users/' . $user_id);
    }

    public function me()
    {
        $this->clearCache('/api/users/me');
        $out = $this->getUserEndpoint('/api/users/me');

        return (object) $out;
    }

    public function user(array $parameters = [])
    {
        $out = $this->getClientEndpoint('/api/users/' . $parameters['user_id'], $parameters);
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

    public function update(int $user_id, array $parameters = [])
    {
        $this->postUserEndpoint('/api/users/' . $user_id, 'PUT', $parameters);
        $this->clearCache('/api/users');
        $this->clearCache('/api/users/' . $user_id);
    }
}
