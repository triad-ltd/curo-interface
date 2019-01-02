<?php

namespace TriadLtd\CuroInterface;

class Accounts extends CuroInterface
{
    public function accounts($parameters = [])
    {
        $out = $this->getClientEndpoint('/api/accounts', $parameters);
        return (object) $out;
    }
}
