<?php

namespace TriadLtd\CuroInterface;

class Users extends CuroInterface
{
    public function me()
    {
        $out = $this->getUserEndpoint('/api/users/me');
        return (object) $out;
    }
}
