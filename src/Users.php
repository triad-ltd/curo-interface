<?php

namespace TriadLtd\CuroInterface;

class Users extends CuroInterface
{
    public function delete(int $user_id)
    {
        $out = $this->postUserEndpoint('/api/v1/users/' . $user_id, 'DELETE');
        $this->clearCache('/api/v1/users');
        $this->clearCache('/api/v1/users/' . $user_id);
    }

    public function me()
    {
        $this->clearCache('/api/v1/users/me');
        $out = $this->getUserEndpoint('/api/v1/users/me');

        return (object) $out;
    }

    public function user(array $parameters = [])
    {
        $out = $this->getClientEndpoint('/api/v1/users/' . $parameters['user_id'], $parameters);
        return (object) $out;
    }

    public function users(array $parameters = [])
    {
        $out = $this->getClientEndpoint('/api/v1/users', $parameters);
        return (object) $out;
    }

    public function store(array $parameters = [])
    {
        $this->postUserEndpoint('/api/v1/users', 'POST', $parameters);
        $this->clearCache('/api/v1/users');
    }

    public function update(int $user_id, array $parameters = [])
    {
        $this->postUserEndpoint('/api/v1/users/' . $user_id, 'PUT', $parameters);
        $this->clearCache('/api/v1/users');
        $this->clearCache('/api/v1/users/' . $user_id);
    }
}
