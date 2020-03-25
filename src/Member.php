<?php
namespace TriadLtd\CuroInterface;
use TriadLtd\CuroInterface\InterfaceCRUD;

class Member extends InterfaceCRUD
{
    private $schema = [
        'members' => [
            'ref_id' => 'member_id',
            'ref_group_id' => 'group_id',
            'title' => 'screen_name',
            'intro' => 'm_field_id_5',
            'phone' => 'm_field_id_6',
            'email' => 'email',
            'website' => 'm_field_id_9',
            'address_1' => 'm_field_id_8',
            'postcode' => 'm_field_id_14',
            'lat' => 'm_field_id_17',
            'lng' => 'm_field_id_18',
            'category' => 'm_field_id_15',
            'logo' => 'avatar_filename',
            'member_type' => 'm_field_id_11'  // sys ref and is not required by Curo API
        ]
    ];

    public function __construct($val)
    {
        parent::__construct($val);
    }

    public function store()
    {
        $fieldsetName = 'members';
        $schema = $this->schema[$fieldsetName];
        $chambers = $this->locateChambers($schema['member_type']);
        $group = $this->getGroup();

        if (!count($group)) return;
        foreach ($this->session['fieldsets'] as $chamber => $fieldset) {
            if (!isset($fieldset[$fieldsetName])) continue;
            // debug
            // if ($chamber != 'NN') continue;

            $form = [];
            $this->renderForm($form, $fieldset[$fieldsetName]['fields'], $schema);

            if (!isset($form['category'])) { echo $this->val['screen_name']; echo " NoCat \n"; continue; }
            if (!isset($form['ref_id'])) { echo $this->val['screen_name']; echo " NoRef \n";  continue; }
            if (isset($form['logo']) && $form['logo']) $form['logo'] = ee()->config->item('avatar_url') . $form['logo'];

            $fieldsetUUID = $fieldset[$fieldsetName]['uuid'];

            $r = $this->curl($chamber, "/accounts?fieldset={$fieldsetUUID}&ref_id={$form['ref_id']}", 'GET');
            if (isset($r['error']) && $r['error']) { print_r($r); continue; }
            if (count($r['data']) > 1) { echo $this->val['screen_name']; echo " " . count($r['data']);  echo " NoMany \n"; continue; } //ambiguous, silently skipped

            $form['category'] = $this->getCategories($chamber, 'categories', explode(',', $form['category']));

            if (count($r['data']) == 1 && !in_array($chamber, $chambers)) {
                $this->curl($chamber, "/accounts/{$r['data'][0]['uuid']}", 'DELETE');
                echo $this->val['screen_name']; echo "NoLive";
                continue;
            }
            if (!in_array($group['group_title'],  ['Members', 'Super Admin'])) {
                // print('delete non member');
                if (count($r['data'])) $this->curl($chamber, "/accounts/{$r['data'][0]['uuid']}", 'DELETE');
                echo $this->val['screen_name']; echo " NoMemb-" . $group['group_title'];
                continue;
            }
            if (count($r['data']) == 0) {
                $form['fieldset_id'] = $fieldset[$fieldsetName]['uuid'];
                $this->curl($chamber, "/accounts", 'POST', $form);
                echo "\n Saved ..." . $form['ref_id'];
            } else {
                $this->curl($chamber, "/accounts/{$r['data'][0]['uuid']}", 'PUT', $form);
                echo "\n Updated ..." . $form['ref_id'];
            }
        }
        // die();
    }

    public function destroy()
    {
        $fieldsetName = 'members';
        $schema = $this->schema[$fieldsetName];
        $chambers = $this->locateChambers($schema['member_type']);

        foreach ($this->session['fieldsets'] as $chamber => $fieldset) {
            if (!in_array($chamber, $chambers)) continue;
            if (!isset($fieldset[$fieldsetName])) continue;

            $form = [];
            $this->renderForm($form, $fieldset[$fieldsetName]['fields'], $schema);

            if (!isset($form['ref_id'])) continue;

            $fieldsetUUID = $fieldset[$fieldsetName]['uuid'];

            $r = $this->curl($chamber, "/accounts?fieldset={$fieldsetUUID}&ref_id={$form['ref_id']}", 'GET');
            if (isset($r['error']) && $r['error']) { print_r($r); continue; }
            if (count($r['data']) > 1 || count($r['data']) === 0) continue; //ambiguous, silently skipped

            $this->curl($chamber, "/accounts/{$r['data'][0]['uuid']}", 'DELETE');

        }
    }

    public function locateChambers($fieldID)
    {
        $chambers = [];
        $region = $this->val[$fieldID];

        foreach($this->chambers as $key => $labels) {
            if (in_array($region, $labels)) $chambers[] = $key;
        }

        if ($region && count($chambers) === 0) {
            $chambers = array_keys($this->chambers);
        }

        return $chambers;
    }
}

