<?php

namespace TriadLtd\CuroInterface;

class Accounts extends CuroInterface
{
    public function accounts($parameters = [])
    {
        $out = $this->getClientEndpoint('/api/v1/accounts', $parameters);
        return (object) $out;
    }

    public function store(array $parameters = [])
    {
        $this->postUserEndpoint('/api/v1/accounts', 'POST', $parameters);
        $this->clearCache('/api/v1/accounts');
    }

    public function update(string $account_uuid, array $parameters = [])
    {
        $out = $this->postClientEndpoint('/api/v1/accounts/' . $account_uuid, 'PUT', $parameters);
        $this->clearCache('/api/v1/accounts');
        $this->clearCache('/api/v1/accounts/' . $account_uuid);
        return (object) $out;
    }

    public function delete(int $account_id)
    {
        $out = $this->postUserEndpoint('/api/v1/accounts/' . $account_id, 'DELETE');
        $this->clearCache('/api/v1/accounts');
        $this->clearCache('/api/v1/accounts/' . $account_id);
    }
}
