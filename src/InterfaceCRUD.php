<?php
namespace TriadLtd\CuroInterface;
use GuzzleHttp\Client;

abstract class InterfaceCRUD
{
    protected $session;
    protected $chambers;
    protected $fieldsets;
    protected $url;
    protected $val;

    public function __construct($val)
    {
        $this->val = $val;
        $this->httpClient = new Client;
    }

    abstract protected function store();
    abstract protected function destroy();
    // abstract protected function bulkDestroy();

    public function setSession(&$session)
    {
        $this->session = &$session;
    }

    public function setChambers($chambers)
    {
        $this->chambers = $chambers;
    }

    public function setFieldsets($labels)
    {
        $this->fieldsets = $labels;
    }

    public function setURL($URL)
    {
        $this->url = $URL;
    }

    protected function renderForm(&$form, $fields, $schema)
    {
        foreach ($fields as $name => $setup) {
            if (!isset($schema[$name])) continue;
            if (!isset($this->val[$schema[$name]])) continue;

            $form[$name] = $this->val[$schema[$name]];
        }
    }

    public function curl($chamber, string $endpoint, string $verb = 'POST', array $parameters = [])
    {
        $endpoint = $this->url . $endpoint;
        $parameters = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->session['chambers'][$chamber],
            ],
            'form_params' => $parameters,
        ];

        try {
            $response = $this->httpClient->request($verb, $endpoint, $parameters);
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(), 'success' => false,
                // 'conent' => $e->getResponse()->getBody()->getContents(),
                'request' => [$endpoint, $parameters]
            ];
        }
    }

    public function getCategories($chamber, $schemaName, array $list)
    {
        $result = [];
        if (!isset($this->session['fieldsets'][$chamber])) return $result;
        if (!isset($this->session['fieldsets'][$chamber][$schemaName])) return $result;

        $fieldsetID = $this->session['fieldsets'][$chamber][$schemaName]['id'];

        $in  = str_repeat('?,', count($list) - 1) . '?';
        $sql = "SELECT `cat_id` FROM `exp_category_field_data` WHERE `field_id_1` IN ({$in}) ";
        $query = ee()->db->query($sql, $list);

        foreach($query->result_array() as $row) {
            $r = $this->curl($chamber, "/categories?fieldset_id={$fieldsetID}&ref_id={$row['cat_id']}", 'GET');
            if (count($r) === 1 && !isset($r['error'])) $result[] = $r[0];
        }

        return $result;
    }

    public function getGroup()
    {
        $result = [];

        if (!isset($this->val['group_id'])) return $result;

        $sql = "SELECT * FROM `exp_member_groups` where `group_id`  = ? ";
        $query = ee()->db->query($sql, $this->val['group_id']);

        foreach($query->result_array() as $row) {
            $result = $row;
        }

        return $result;
    }
}

