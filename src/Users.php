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

    public function store(string $account_uuid, array $parameters = [])
    {
        $this->postClientEndpoint('/api/v1/accounts/' . $account_uuid . '/users', 'POST', $parameters);
        $this->clearCache('/api/v1/users');
    }

    public function update(string $account_uuid, string $user_uuid, array $parameters = [])
    {
        $out = $this->postClientEndpoint('/api/v1/accounts/' . $account_uuid . '/users/' . $user_uuid, 'PUT', $parameters);
        $this->clearCache('/api/v1/users');
        $this->clearCache('/api/v1/users/' . $user_uuid);
        return (object) $out;
    }
}
