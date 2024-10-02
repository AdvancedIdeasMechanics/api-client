# API Client #
An API client primarily to work with DotKernel API. https://github.com/dotkernel/api

## Install ##
Install via composer

### Composer ###

`composer install advancedideasmechanics/apiclient`

### Call package ###

#### Initialize ####
`$apiclient = new ApiClient(baseurl, clientId, clientSecret, grantType, scope, userName, userSecret, tokenLocation, tokenFilename, debug, additionalParams);`

See if it works, if api call works it will create a tokenFilename in your chosen location. Create location path if not already in place.

#### Test ####
`$apiclient->getAccessToken();`

#### Send data to API ####

`$apiclient->makeApiRequest($endpoint, $body, $method = "GET", $additionalHeaders = []);`

#### Initialize Details ####
Below uses some DotKernel defaults. DO NOT use those values in production. 

`baseurl  = http://localhost:8083`

`clientId = frontend` # DotKernel default

`clientSecret = frontend` # DotKernel default

`grantType = password` 

`scope = api` # DotKernel default (you can keep this)

`userName = test@dotkernel.com` # DotKernel default

`userSecret = dotkernel` # DotKernel default

`tokenLocation = /var/www/data/` # keep outside web folder

`tokenFilename = token.json` # can be any file name you wish.

`debug = false` # optional will default to false, this for guzzle debugging to log files.

`additionParms = []` # Optional Guzzle parameters.

#### Send / Retrieve to API ####
`endpoint = /someapiendpoint`

`body = []` # Array of information that will be sent to api as json_encoded() can be just [] if using GET

`method = GET` # defaults to GET but any VERB you set for endpoint to accept

`addtionalHeader = []` # Optional for Guzzle