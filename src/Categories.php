<?php

namespace TriadLtd\CuroInterface;

class Categories extends CuroInterface
{
    public function categories($parameters = [])
    {
        $out = $this->getClientEndpoint('/api/v1/categories', $parameters);

        return (object) $out;
    }
}
