<?php

namespace TriadLtd\CuroInterface;

class Taxonomies extends CuroInterface
{
    public function attachChild($taxonomy_id, $parent_id, $child_id)
    {
        $this->postClientEndpoint('/api/v1/taxonomies/' . $taxonomy_id . '/' . $parent_id . '/attach/' . $child_id);
    }

    public function detachChild($taxonomy_id, $parent_id, $child_id)
    {
        $this->postClientEndpoint('/api/v1/taxonomies/' . $taxonomy_id . '/' . $parent_id . '/detach/' . $child_id);
    }

    public function getChildren($taxonomy_id, $parent_id)
    {
        $out = $this->getClientEndpoint('/api/v1/taxonomies/' . $taxonomy_id . '/' . $parent_id);

        return (object) $out;
    }

    public function purgeChildren($taxonomy_id)
    {
        $this->getClientEndpoint('/api/v1/taxonomies/' . $taxonomy_id . '/purge');

        return true;
    }

    public function checkExistence($taxonomy_id, $parent_id, $child_id)
    {
        $out = $this->getClientEndpoint('/api/v1/taxonomy_exists', ['taxonomy_id' => $taxonomy_id, 'parent_id' => $parent_id, 'child_id' => $child_id]);

        return (int) $out;
    }
}
