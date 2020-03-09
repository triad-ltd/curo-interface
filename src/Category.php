<?php
namespace TriadLtd\CuroInterface;
use TriadLtd\CuroInterface\InterfaceCRUD;

class Category extends InterfaceCRUD
{
    private $schema = [
        'categories' => [
            'ref_id' => 'cat_id',
            'title' => 'cat_name',
            'code' => 'field_id_1',
            'ref_group_id' => 'group_id',
            'ref_parent_id' => 'parent_id'
        ]
    ];

    public function __construct($val)
    {
        parent::__construct($val);
    }

    public function store()
    {
        $schema = $this->schema['categories'];

        foreach ($this->session['fieldsets'] as $chamber => $fieldset) {
            if (!isset($fieldset['categories'])) continue;

            $form = ['label' => $this->val['cat_name']];
            $this->renderForm($form, $fieldset['categories']['fields'], $schema);

            if (!isset($form['ref_id'])) continue;

            $fieldsetID = $fieldset['categories']['id'];
            $r = $this->curl($chamber, "/categories?fieldset_id={$fieldsetID}&ref_id={$form['ref_id']}", 'GET');

            if (isset($r['error']) && $r['error']) { print_r($r); continue; }
            if (count($r) > 1) continue; //ambiguous silently skipped

            if (count($r) == 0) {
                $form['fieldset_id'] = $fieldset['categories']['uuid'];
                $this->curl($chamber, "/categories", 'POST', $form);
            } else {
                $this->curl($chamber, "/categories/{$r[0]['uuid']}", 'PUT', $form);
            }
        }
    }

    public function destroy()
    {
        $schema = $this->schema['categories'];

        foreach ($this->session['fieldsets'] as $chamber => $fieldset) {
            if (!isset($fieldset['categories'])) continue;

            $this->renderForm($form, $fieldset['categories']['fields'], $schema);

            if (!isset($form['ref_id'])) continue;

            $fieldsetID = $fieldset['categories']['id'];
            $r = $this->curl($chamber, "/categories?fieldset_id={$fieldsetID}&ref_id={$form['ref_id']}", 'GET');

            if (isset($r['error']) && $r['error']) { print_r($r); continue; }
            if (count($r) > 1 || count($r) == 0) continue; //ambiguous silently skipped

            $this->curl($chamber, "/categories/{$r[0]['uuid']}", 'DELETE');
        }
    }
}



