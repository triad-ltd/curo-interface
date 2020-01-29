<?php

namespace TriadLtd\CuroInterface;

use GuzzleHttp\Client;

class CuroInterface
{
    private $api_url;
    private $api_client_id;
    private $api_client_Secret;
    private $session;
    private $cache_enabled = true;

    /**
     * Curo Interface Class
     *
     * @param array  $params   Array of parameters requried to start the interface.
     */
    public function __construct(array $params = null)
    {
        $this->session = &$_SESSION['curo'];

        if (!empty($params)) {
            if (empty($params['api_url'])) {
                die('Curo api url required');
            }
            if (empty($params['client_id'])) {
                die('Curo client id required');
            }
            if (empty($params['client_secret'])) {
                die('Curo client secret required');
            }
            $this->session['api_url'] = $params['api_url'];
            $this->session['api_client_id'] = $params['client_id'];
            $this->session['api_client_secret'] = $params['client_secret'];

            if (isset($params['cache_enabled']) && $params['cache_enabled'] == false) {
                $this->cache_enabled = false;
            }
        }

        $this->httpClient = new Client;

        if (empty($this->session['client_access_token'])) {
            if (empty($this->session['api_url'])) {
                die('Curo api url not set');
            }
            if (empty($this->session['api_client_id'])) {
                die('Curo client id not set');
            }
            if (empty($this->session['api_client_secret'])) {
                die('Curo client secret not set');
            }
            $this->getClientAccessToken();
        }
    }

    public function clearCache($endpoint = null)
    {
        if (empty($endpoit)) {
            $this->session['cache'] = null;
        } else {
            $this->session['cache'][$endpoint] = null;
        }
    }

    public function logoutUser()
    {
        $this->session['username'] = null;
        $this->session['oauth'] = null;
        $this->session['user'] = null;
    }

    public function getClientAccessToken()
    {
        $parameters = [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->session['api_client_id'],
                'client_secret' => $this->session['api_client_secret'],
                'scope' => '',
            ],
        ];
        try {
            $response = $this->httpClient->post($this->session['api_url'] . '/api/v1/oauth/token', $parameters);
            $data = json_decode((string) $response->getBody(), true);
            $this->session['client_access_token'] = $data['access_token'];
        } catch (\Exception $e) {
            echo ($e->getMessage());
            die();
        }
    }

    public function getClientEndpoint($endpoint, $parameters = [])
    {
        $hash = hash('md5', serialize($parameters));

        if (!$this->cache_enabled || empty($this->session['cache'][$endpoint][$hash])) {
            $parameters = [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => $this->session['client_access_token'],
                ],
                'query' => $parameters,
            ];

            try {
                $request_url = $this->session['api_url'] . $endpoint;
                $response = $this->httpClient->request('GET', $request_url, $parameters);

                $this->session['cache'][$endpoint][$hash] = $response->getBody()->getContents();
            } catch (\Exception $e) {
                echo ($e->getMessage());
                die();
                dump($endpoint);
                dump($parameters);
                dump($e->getResponse()->getBody()->getContents());
            }
        }

        return json_decode($this->session['cache'][$endpoint][$hash]);
    }

    public function postClientEndpoint(string $endpoint, string $verb = 'POST', array $parameters = [])
    {
        $parameters = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->session['client_access_token'],
            ],
            'form_params' => $parameters,
        ];

        try {
            $response = $this->httpClient->request(
                $verb,
                $this->session['api_url'] . $endpoint,
                $parameters
            );

            return true;
        } catch (\Exception $e) {
            dump($endpoint);
            dump($parameters);
            dump($e->getResponse()->getBody()->getContents());
        }
    }

    public function getPasswordToken(string $username, string $password)
    {
        // used when performing login.
        $parameters = [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $this->session['api_client_id'],
                'client_secret' => $this->session['api_client_secret'],
                'username' => $username,
                'password' => $password,
                'scope' => '',
            ],
        ];

        try {
            $response = $this->httpClient->request(
                'POST',
                $this->session['api_url'] . '/api/v1/oauth/token',
                $parameters
            );

            $data = json_decode((string) $response->getBody(), true);

            $this->session['username'] = $username;
            $this->session['oauth']['token_type'] = $data['token_type'];
            $this->session['oauth']['expires_in'] = $data['expires_in'];
            $this->session['oauth']['access_token'] = $data['access_token'];
            $this->session['oauth']['refresh_token'] = $data['refresh_token'];
            $interface = new Users();
            $this->session['user'] = $interface->me();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getUserEndpoint($endpoint, $parameters = [])
    {
        $hash = hash('md5', serialize($parameters));

        if (!$this->cache_enabled || empty($this->session['cache'][$endpoint][$hash])) {
            $parameters = [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->session['oauth']['access_token'],
                ],
                'query' => $parameters,
            ];

            try {
                $response = $this->httpClient->request('GET', $this->session['api_url'] . $endpoint, $parameters);
                $this->session['cache'][$endpoint][$hash] = $response->getBody()->getContents();
            } catch (\Exception $e) {
                dump($endpoint);
                dump($parameters);
                dump($e->getResponse()->getBody()->getContents());
            }
        }

        return json_decode($this->session['cache'][$endpoint][$hash]);
    }

    public function postUserEndpoint(string $endpoint, string $verb = 'POST', array $parameters = [])
    {
        $endpoint = $this->session['api_url'] . $endpoint;
        $parameters = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->session['oauth']['access_token'],
            ],
            'form_params' => $parameters,
        ];

        try {
            $response = $this->httpClient->request($verb, $endpoint, $parameters);
            $data = json_decode($response->getBody()->getContents());

            return $data;
        } catch (\Exception $e) {
            dump($endpoint);
            dump($parameters);
            dump($e->getResponse()->getBody()->getContents());
        }
    }
}
