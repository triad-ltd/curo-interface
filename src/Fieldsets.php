<?php
namespace TriadLtd\CuroInterface;

trait Fieldsets
{
    protected $fieldsets = [];

    public function registerFieldsets($labels)
    {
        $this->fieldsets = $labels; 

        if (isset($this->session['fieldsets']) && count($this->session['fieldsets'])) return $this;
        $this->session['fieldsets'] = [];
     
        foreach ($this->session['chambers'] as $chamber => $accessToken) {
            $this->session['fieldsets'][$chamber] = $this->getAvailableFieldsets($accessToken, array_Keys($labels));
        } 

        foreach ($this->session['fieldsets'] as $chamber => $fieldsets) {
            $accessToken = $this->session['chambers'][$chamber];
            $params = $this->getParams($accessToken); 
            $this->getFields($chamber, $fieldsets, $params);
        }
    }

    private function getAvailableFieldsets($accessToken, $labels)
    {
        $body = [];
        $result = [];
        $url = $this->url . '/fieldsets/';
        $params = $this->getParams($accessToken); 

        $response = $this->httpClient->request('GET', $url, $params);

        try {
            $body = json_decode((string) $response->getBody()->getContents() , true);
        } catch (\Exception $e) {
            echo ($e->getMessage());
            return [];
        }

        foreach ($body as $fieldset) {
            if (in_array($fieldset['label'], $labels)) {
                $result[$fieldset['label']] = [
                    'id' => $fieldset['id'],
                    'uuid' => $fieldset['uuid'],
                    'type' => $fieldset['type'],
                    'fields' => []
                ];
            }
        }
      
        return $result;
    }

    private function getFields($chamber, $fieldsets, $params)
    {
        foreach ($fieldsets as $label => $fieldset) {
            $body = [];
            $url = $this->url . '/fieldsets/' . $fieldset['uuid'] . '?with_fields=true';
            $response = $this->httpClient->request('GET', $url, $params);

            try {
                $body = json_decode((string) $response->getBody()->getContents() , true);
            } catch (\Exception $e) {
                echo ($e->getMessage());
                continue; 
            }

            if ($body['fields']) {
                $fields = [];

                foreach ($body['fields'] as $field) {
                    $fields[$field['slug']] = [
                        'uuid' => $field['uuid'],
                        'settings' => $field['settings'],
                        'data_field' => $field['type']['data_field'],
                    ];
                }
                $fieldsets[$label]['fields'] = $fields;
            }
        }

        $this->session['fieldsets'][$chamber] = $fieldsets;
    }
}
