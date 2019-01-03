<?php

namespace TriadLtd\CuroInterface;

class Taxonomies extends CuroInterface
{
    public function attachChild($taxonomy_id, $parent_id, $child_id)
    {
        $this->postClientEndpoint('/api/taxonomies/' . $taxonomy_id . '/' . $parent_id . '/attach/' . $child_id);
    }

    public function detachChild($taxonomy_id, $parent_id, $child_id)
    {
        $this->postClientEndpoint('/api/taxonomies/' . $taxonomy_id . '/' . $parent_id . '/detach/' . $child_id);
    }

    public function getChildren($taxonomy_id, $parent_id)
    {
        $out = $this->getClientEndpoint('/api/taxonomies/' . $taxonomy_id . '/' . $parent_id);

        return (object) $out;
    }

    public function purgeChildren($taxonomy_id)
    {
        $this->getClientEndpoint('/api/taxonomies/' . $taxonomy_id . '/purge');

        return true;
    }
}
