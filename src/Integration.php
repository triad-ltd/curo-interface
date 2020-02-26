<?php
namespace TriadLtd\CuroInterface;
use GuzzleHttp\Client;
use TriadLtd\CuroInterface\Auth;
use TriadLtd\CuroInterface\Fieldsets;

class Integration
{
    use Auth, Fieldsets;

    private $session;
    private $url;

    public function __construct(array $params = null, &$session)
    {
        $this->session = &$session;
        $this->httpClient = new Client;
        $this->url = $params['api_url'];
    }

    public function store($adapter)
    {
        $this->initAdapter($adapter);
        $adapter->store();
    }

    public function destroy($adapter)
    {
        $this->initAdapter($adapter);
        $adapter->destroy();
    }

    public function bulkDestroy($adapter)
    {
        echo "-- bulk destroy --";
        die(get_class($adapter));
    }

    private function initAdapter($adapter)
    {
        $adapter->setSession($this->session);
        $adapter->setChambers($this->chambers);
        $adapter->setFieldsets($this->fieldsets);
        $adapter->setURL($this->url);
    }

    private function getParams($accessToken)
    {
        return [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ];
    }
}

