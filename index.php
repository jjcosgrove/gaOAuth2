<?php
    /**
     * an example of how to use gaOAuth2 with the Google Analytics API
     *
     * @author Jonathan James Cosgrove
     * @version 1.0.0
     * @link https://github.com/jjcosgrove/gaOAuth2
     */
    
    //include gaOAuth2
    require('gaOAuth2/gaOAuth2.php');

    //optional - used for 'PrettyPrint' function
    require('gaOAuth2/helperFunctions.php');

    //configuration for the GA API - replace with your actual credentials.
    //visit: https://console.developers.google.com and set up your project
    //and OAuth details first.
    $myGAOAuth2Config = array(
        'gaClientId'                => 'YOUR_CLIENT_ID_GOES_HERE',
        'gaClientSecret'            => 'YOUR_CLIENT_SECRET_GOES_HERE',

        //the file within which the original require is placed, i.e: require('gaOAuth2.php')
        'gaRedirectURI'             => 'YOUR_DOMAIN_ID_GOES_HERE',

        //path relative to root domain:
        'gaOAuth2CredentialsJSON'   => 'gaOauth2/credentials/YOUR_CREDENTIALS_FILENAME_GOES_HERE.json'
    );

    //create a new instance
    $myGAOAuth2 = new gaOAuth2($myGAOAuth2Config);

    //authorize: if not yet done, prompt the user for permission
    //saves the credentials to the specific JSON file (see config)
    $myGAOAuth2->Authorize();

    //debugging: output the OAuth2 object to check it's values
    PrettyPrint($myGAOAuth2);

    //a simple google analytics query (substitute your property id from GA)
    $myGAQuery  = '?ids=ga:123456789';
    $myGAQuery .= '&metrics=ga:pageviews,ga:uniquePageviews,ga:avgTimeOnPage';
    $myGAQuery .= '&filters=ga:country==United Kingdom';
    $myGAQuery .= '&start-date=2015-04-01';
    $myGAQuery .= '&end-date=2015-05-07';
    $myGAQuery .= '&max-results=50';

    //perform the API request and store the response
    $myGAOAuth2Response = $myGAOAuth2->Request($myGAQuery);

    //debugging: output the response
    PrettyPrint($myGAOAuth2Response);
?>
