PSR compliant interface to Curo core platform

Create an instance passing an array of settings:
[
    'cache_enabled' => false, // boolean, optional, defaults to true
    'api_url'       => 'https://curo.triad.uk.com/api/v1', // string, required
    'client_id'     => 0, // integer, required
    'client_secret' => 'yoursecretstring', // string, required
]