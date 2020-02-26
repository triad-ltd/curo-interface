<?php
namespace TriadLtd\CuroInterface;

trait Auth
{
    private $chambers;

    public function authenticateChambers($auth, $chambers)
    {
        $this->chambers = $chambers;
        $credentials = [];

        if (isset($this->session['chambers']) && count($this->session['chambers'])) return $this;

        $this->session['chambers'] = [];

        $respond = $this->authenticateChamber($auth['client_id'], $auth['client_secret']);

        if (count($respond) && isset($respond['access_token'])) {

            $credentials = $this->getChambersCredentials($respond['access_token'], $chambers);
        }

        foreach($credentials as $key => $credential) {

            $respond = $this->authenticateChamber($credential['id'], $credential['secret']);
            if ($respond['access_token']) $this->session['chambers'][$key] = $respond['access_token'];
        }
    }

    private function getChambersCredentials($accessToken, $chambers)
    {
        $credentials = [];
        $body = [];

        $url = $this->url . '/clients/chambers-users';
        $params = $this->getParams($accessToken); 

        $response = $this->httpClient->request('GET', $url, $params);

        try {
            $body = json_decode((string) $response->getBody()->getContents() , true);
        } catch (\Exception $e) {
            echo ($e->getMessage());
            return [];
        }

        if (isset($body['success']) && $body['success'] && isset($body['content'])) {

            foreach($body['content'] as $r) {
                foreach($chambers as $key => $labels) {

                    if (in_array($r['name'], $labels)) {
                        $credentials[$key] = $r;
                        continue;
                    }
                }
            }
        }

        return $credentials;
    }
  

    private function authenticateChamber($id, $secret)
    {
        $params = [
            'form_params' => [
                'grant_type' => 'client_credentials', 'client_id' => $id, 'client_secret' => $secret
            ]
        ];

        $response = $this->httpClient->post($this->url . '/oauth/token', $params);

        try {
            return json_decode((string) $response->getBody(), true);
        } catch (\Exception $e) {
            echo ($e->getMessage());
            return [];
        }
    }
}
