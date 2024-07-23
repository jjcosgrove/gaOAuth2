# gaOAuth2

gaOAuth2 is a simple helper class for working with the Google Analytics API.

Release information:
* Current Version: 1.0.0

## Setup

* Place the files within your desired webroot.
* Configure the gaOAuth2 object via constructor (see Google GA API docs for help).
* Ensure permissions are correct for read/write by your server (for storing credentials).
* Visit the page - if it is your first run then you will be prompted to authenticate.
* Once authenticated, your GA API credentials will be stored for future use and refreshed when needed.

## Usage

```php
require('gaOAuth2/gaOAuth2.php');

$myGAOAuth2Config = array(
    'gaClientId'                => 'YOUR_CLIENT_ID_GOES_HERE',
    'gaClientSecret'            => 'YOUR_CLIENT_SECRET_GOES_HERE',
    'gaRedirectURI'             => 'YOUR_DOMAIN_GOES_HERE',
    'gaOAuth2CredentialsJSON'   => 'YOUR_CREDENTIALS_FILENAME_GOES_HERE'
);

$myGAOAuth2 = new gaOAuth2($myGAOAuth2Config);
$myGAOAuth2->Authorize();

$myGAQuery  = '?ids=ga:123456789';
$myGAQuery .= '&metrics=ga:pageviews,ga:uniquePageviews,ga:avgTimeOnPage';
$myGAQuery .= '&filters=ga:country==United Kingdom';
$myGAQuery .= '&start-date=2015-04-01';
$myGAQuery .= '&end-date=2015-05-07';
$myGAQuery .= '&max-results=50';

$myGAOAuth2Response = $myGAOAuth2->Request($myGAQuery);
```

The included 'index.php' file provides a rough framework/example to get you started.

## Contact

Please send feedback, comments and suggestions to my email address which can be found within the gaOAuth2 class definition.

Bugs or feature requests/contributions can be done via:

[https://github.com/jjcosgrove/gaOAuth2/issues](https://github.com/jjcosgrove/gaOAuth2/issues)

## Authors

* Just me for now.
