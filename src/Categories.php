<?php

namespace TriadLtd\CuroInterface;

class Categories extends CuroInterface
{
    public function categories($parameters = [])
    {
        $out = $this->getClientEndpoint('/api/categories', $parameters);
        return (object) $out;
    }
}
