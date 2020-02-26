<?php
namespace TriadLtd\CuroInterface;
use TriadLtd\CuroInterface\InterfaceCRUD;

class Channel extends InterfaceCRUD
{
    private $paths = [
        "events" => [
            'book_now_url' => '/events/detail/',
            'isCancel' => 'field_id_65'
        ],
        "trainings" => [
            'book_now_url' => '/training/detail/',
        ],
    ];

    private $schema = [
        "news" => [
            'title' => 'title',
            'url_title' => 'url_title',
            'content' => 'field_id_32',
            'icon_url' => 'field_id_33', // <- "{filedir_6}inovacijos-83332335.jpg"
            'origin' => 'field_id_41',
            'date' => 'entry_date',
            'expiration_date' => 'expiration_date',
            'channel_id' => 'channel_id', //<- 5 (events, training, news, member2member_offers)
            'status' => 'status', // <- open, closed
            'category' => 'cat_group_id_1',  // <- "1|2" (northampton, milton keys)
            'member' => 'author_id',
            'ref_id' => 'entry_id'
        ],
        "events" => [
            'title' => 'title',
            'book_now_url' => 'url_title',
            'venue' => 'field_id_5', // "Site: Northampton Rugby Football Club"
            'non_member_rate' => 'field_id_9', // "15.00"
            'cost' => 'field_id_10', // "10.00"
            'event_type' => 'field_id_21',
            'host' => 'field_id_22',
            'organiser' => 'field_id_23',
            'total_place' => 'field_id_24', //  "50"
            'total_avail' => 'field_id_64', // <- "13"
            'cancelled' => 'field_id_65', // ('true','false')
            'arrive_time' => 'field_id_18', // "17:30"
            'start_time' => 'field_id_19',
            'end_time' => 'field_id_20',
            'date' => 'entry_date',
            'end_date' => 'expiration_date',
            'channel_id' => 'channel_id', //<- 5 (events, training, news, member2member_offers)
            'status' => 'status', // <- open, closed
            'category' => 'cat_group_id_1',  // <- "1|2" (northampton, milton keys)
            'member' => 'author_id',
            'ref_id' => 'entry_id'
        ],
        "trainings" => [
            'title' => 'title',
            'book_now_url' => 'url_title',
            'date' => 'entry_date', // => 1590620400 { time is not taken into consideration }
            'venue' => 'field_id_5', // <- 'Site: Northamptonshire Chamber'
            'cost' => 'field_id_10', // <- 299.00
            'cost_non_member' => 'field_id_9', // <- 369.00
            'is_canceled' => 'field_id_65', //  <- ('true' || '')
            'total_place' => 'field_id_24', // <- 12
            'total_avail' => 'field_id_64', // <- 7
            'arrive_time' => 'field_id_18', // => 09:15
            'start_time' => 'field_id_19', // => 09:30
            'end_time' => 'field_id_20', // => 09:30
            'date' => 'entry_date', // <- 1585094400
            'end_date' => 'expiration_date', // <- 1585164600
            'channel_id' => 'channel_id', //<- 5 (events, training, news, member2member_offers)
            'status' => 'status', // <- open, closed
            'category' => 'cat_group_id_1', // <-"1|2"
            'member' => 'author_id',
            'ref_id' => 'entry_id',
        ],
        "m2m" => [
            'title' => 'title',
            'content' => 'field_id_42', //  <- ' content \n conent'
            'status' => 'status', // open
            'channel_id' => 'channel_id',
            'date' => 'entry_date', // => 1582030380
            'end_date' => 'expiration_date', // => 1582980840
            'channel_id' => 'channel_id', //<- 5 (events, training, news, member2member_offers)
            'status' => 'status', // <- open, closed
            'category' => 'cat_group_id_1', //  => 1|2
            'member' => 'author_id', // <- 1
            'ref_id' => 'entry_id', // <- 182221
        ]
    ];

    private $actions = [
        'news' => 'content',
        'events' => 'events',
        'trainings' => 'events',
        'm2m' => 'events',
    ];

    public function __construct($val)
    {
        parent::__construct($val);
        ee()->load->library('typography');
    }

    public function store()
    {
        $schema = $this->locateChannelSchema();
        if (count($schema) === 0) return;

        $chambers = $this->locateChambers($schema);
        if (count($chambers) === 0) return;

        foreach ($this->session['fieldsets'] as $chamber => $fieldsets) {
            if (!in_array($chamber, $chambers)) continue;
            if (!isset($fieldsets[$this->selectedFieldset])) continue;

            $form = [];
            $this->renderForm($form, $fieldsets[$this->selectedFieldset]['fields'], $schema);
            if (!isset($form['ref_id'])) continue;

            $member = $this->locatoteMember($chamber, $schema, $form);
            if (isset($form['content'])) $form['content'] = ee()->typography->parse_file_paths($form['content']);
            if (isset($form['icon_url'])) $form['icon_url'] =  ee()->typography->parse_file_paths($form['icon_url']);
            if (isset($form['date'])) $form['date'] = date('Y-m-d G:i:s', $form['date']);
            if (isset($form['end_date'])) $form['end_date'] = date('Y-m-d G:i:s', $form['end_date']);
            if (isset($this->paths[$this->selectedFieldset])) {
                $paths = $this->paths[$this->selectedFieldset];
                $form['book_now_url'] = $paths['book_now_url'] . $form['book_now_url'];
                if ($this->val[$paths['isCancel']] === 'true') {
                    return $this->destroy();
                }
            }

            if ($this->val['status'] === 'closed') {
                return $this->destroy();
            }

            $fieldsetUUID = $fieldsets[$this->selectedFieldset]['uuid'];

            $controller = $this->actions[$this->selectedFieldset];
            $r = $this->curl($chamber, "/{$controller}?fieldset={$fieldsetUUID}&ref_id={$form['ref_id']}", 'GET');
            print_r($r);
            if (isset($r['error']) && $r['error']) { print_r($r); continue; }
            if (count($r['data']) > 1) continue; //ambiguous, silently skipped
            if (count($r['data']) == 0) {
                $form['fieldset_id'] = $fieldsets[$this->selectedFieldset]['id'];
                if ($controller === 'content') $form['body'] = $form['title'];
                if ($controller === 'events') $form['label'] = $form['title'];
                if ($controller === 'events' && count($form['member'])) $form['author_id'] = $member['id'];
                $save = $this->curl($chamber, "/{$controller}", 'POST', $form);
            } else {
                if ($controller === 'content') $form['body'] = $form['title'];
                if ($controller === 'events') $form['label'] = $form['title'];
                if ($controller === 'events' && count($form['member'])) $form['author_id'] = $member['id'];
                $update = $this->curl($chamber, "/{$controller}/{$r['data'][0]['uuid']}", 'PUT', $form);
            }
        }

        echo "<pre>";
        print_r($form);
        die(' -- store M2M  -- ');
    }

    public function destroy()
    {
        $schema = $this->locateChannelSchema();
        if (count($schema) === 0) return;

        foreach ($this->session['fieldsets'] as $chamber => $fieldsets) {
            if (!isset($fieldsets[$this->selectedFieldset])) continue;

            $form = [];
            $this->renderForm($form, $fieldsets[$this->selectedFieldset]['fields'], $schema);

            $fieldsetUUID = $fieldsets[$this->selectedFieldset]['uuid'];
            $controller = $this->actions[$this->selectedFieldset];

            $r = $this->curl($chamber, "/{$controller}?fieldset={$fieldsetUUID}&ref_id={$form['ref_id']}", 'GET');
            if (isset($r['error']) && $r['error']) { print_r($r); continue; }
            if (count($r['data']) > 1 || count($r['data']) == 0) continue; //ambiguous, silently skipped

            $this->curl($chamber, "/{$controller}/{$r['data'][0]['uuid']}", 'DELETE');
        }
    }

    protected function locateChannelSchema()
    {
        $form = [];

        $sql = "SELECT * FROM `exp_channels` where channel_id = ?";
        $query = ee()->db->query($sql, $this->val['channel_id']);
        $channelName = current($query->result_array())['channel_name'];

        foreach($this->fieldsets as $key => $labels) {
            if (!in_array($channelName, $labels)) continue;
            $form = $this->schema[$key];
            $this->selectedFieldset = $key;
        }

        return $form;
    }

    protected function locateChambers(array $schema)
    {
        $chambers = [];

        if (!isset($schema['category'])) return $chambers;
        if (!isset($this->val[$schema['category']])) return $chambers;

        $list = explode('|', $this->val[$schema['category']]);
        if (count($list) === 0) return $chambers;

        $in  = str_repeat('?,', count($list) - 1) . '?';
        $sql = "SELECT cat_url_title FROM `exp_categories` where cat_id in ({$in})";
        $query = ee()->db->query($sql, $list);

        foreach($query->result_array() as $row) {
            foreach($this->chambers as $chamber => $labels) {
                if (!in_array($row['cat_url_title'], $labels)) continue;
                $chambers[] = $chamber; break;
            }
        }

        return $chambers;
    }

    protected function locatoteMember($chamber, $schema, &$form)
    {
        $fieldsetName = 'members';
        if (!isset($form['member'])) return;
        if (!isset($this->session['fieldsets'][$chamber][$fieldsetName])) return;
        $fieldset =  $this->session['fieldsets'][$chamber][$fieldsetName];
        $fieldsetUUID = $fieldset['uuid'];
        $form['member'] = [];

        $r = $this->curl($chamber, "/accounts?fieldset={$fieldsetUUID}&ref_id={$this->val['author_id']}", 'GET');
        if (isset($r['error']) && $r['error']) { print_r($r); return; }
        if (count($r['data']) > 1) return; //ambiguous, silently skipped
        if (isset($r['data'][0])) $form['member'] = ['uuid' => $r['data'][0]['uuid']];

        return $r['data'][0];
    }
}

