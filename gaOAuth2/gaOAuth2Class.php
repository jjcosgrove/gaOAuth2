<?php
    /**
     * gaOAuth2: the main Google Analytics helper Class
     *
     * stores all the relevant information about your configuration including
     * the credentials currently in use. some defaults are assumed for:
     * gaAPIEndPoint: the default GA API URL
     * gaAuthURLBase, gaTokenURLBase: urls for authentication and token requests
     * gaAPIScope: by default read-only access is granted.
     *
     * 
     *
     * @author Jonathan James Cosgrove (jjcosgrove.inbox@gmail.com)
     * @version 1.0.0
     * @link https://github.com/jjcosgrove/gaOAuth2
     */
    class gaOAuth2 {

        //everything is private. no real need to access individual variables publicly

        //settable via constructor
        private $gaAPIEndPoint              = 'https://www.googleapis.com/analytics/v3/data/ga';
        private $gaAuthURLBase              = 'https://accounts.google.com/o/oauth2/auth';
        private $gaTokenURLBase             = 'https://accounts.google.com/o/oauth2/token';
        private $gaAPIScope                 = 'https://www.googleapis.com/auth/analytics.readonly';
        private $gaClientId                 = '';
        private $gaClientSecret             = ''; 
        private $gaRedirectURI              = '';
        private $gaOAuth2CredentialsJSON    = '';

        //not settable
        private $gaOAuth2Code               = '';
        private $gaAccessToken              = '';
        private $gaRefreshToken             = '';
        private $gaTokenExpiryDate          = '';

        //misc ($gaQuery is of course settable via the constructor)
        private $gaQuery                    = '';
        private $gaResponse                 = '';

        //constructor
        public function __construct($gaOAuth2Config)
        {
            $this->UpdateConfiguration($gaOAuth2Config);
        }

        /**
         * handles the initial authorization and checks for an existing
         * set of credentials in a JSON file. if not found, or has expired
         * it will prompt the user for new access/permission and save the
         * credentials into a JSON file
         */
        public function Authorize()
        {
            if ($this->CredentialsExist() && !$this->CredentialsExpired()) {
                $this->LoadExistingCredentials();
            } else if ($this->CredentialsExist() && $this->CredentialsExpired()){
                $this->LoadExistingCredentials();
                $this->RefreshAndStoreAndUseNewCredentials();
            } else {
                $this->AuthorizeAndStoreAndUseNewCredentials();
            }
        }

        /**
         * performs the API request based on the current gaQuery
         * using the additional HttpClient class for easier GET/POST.
         */
        public function Request($gaQuery)
        {
            $this->gaQuery = $this->GAURLEncode($gaQuery);

            $httpClient = new HttpClient();
            $headers = array(
              'Authorization: Bearer ' . $this->gaAccessToken
            );
            $response = $httpClient->GET($this->gaAPIEndPoint . $this->gaQuery, $headers);
            $this->gaResponse = json_decode($response);

            unset($httpClient);
            return $this->gaResponse;
        }

        /**
         * A little helper to encode the GA API requests. full url_encode breaks things
         *
         * @param [string] $query a string representing the GA query to encode
         */
        private function GAURLEncode($gaQuery){
           return str_replace(array(':',',','==',' '),array('%3A','%2C','%3D%3D','%20'),$gaQuery);
        }

        /**
         * saves all of the user-specified values into the instance.
         * if nothing is specified for a given variable - the default is used.
         *
         * @param [array] $gaOAuth2Config an array of configuration data.
         */
        private function UpdateConfiguration($gaOAuth2Config)
        {
            //store each element of the configuration
            $this->gaAPIEndPoint            = isset($gaOAuth2Config['gaAPIEndPoint'])           ? $gaOAuth2Config['gaAPIEndPoint']          : $this->gaAPIEndPoint;
            $this->gaAuthURLBase            = isset($gaOAuth2Config['gaAuthURLBase'])           ? $gaOAuth2Config['gaAuthURLBase']          : $this->gaAuthURLBase;
            $this->gaTokenURLBase           = isset($gaOAuth2Config['gaTokenURLBase'])          ? $gaOAuth2Config['gaTokenURLBase']         : $this->gaTokenURLBase;
            $this->gaAPIScope               = isset($gaOAuth2Config['gaAPIScope'])              ? $gaOAuth2Config['gaAPIScope']             : $this->gaAPIScope;
            $this->gaClientId               = isset($gaOAuth2Config['gaClientId'])              ? $gaOAuth2Config['gaClientId']             : $this->gaClientId;
            $this->gaClientSecret           = isset($gaOAuth2Config['gaClientSecret'])          ? $gaOAuth2Config['gaClientSecret']         : $this->gaClientSecret;
            $this->gaRedirectURI            = isset($gaOAuth2Config['gaRedirectURI'])           ? $gaOAuth2Config['gaRedirectURI']          : $this->gaRedirectURI;
            $this->gaOAuth2CredentialsJSON  = isset($gaOAuth2Config['gaOAuth2CredentialsJSON']) ? $gaOAuth2Config['gaOAuth2CredentialsJSON']: $this->gaOAuth2CredentialsJSON;
            $this->gaQuery                  = isset($gaOAuth2Config['gaQuery'])                 ? $gaOAuth2Config['gaQuery']                : $this->gaQuery;
        }

        /**
         * checks if a JSON file already exists and is not empty
         */
        private function CredentialsExist()
        {
            return (file_exists($this->gaOAuth2CredentialsJSON) && filesize($this->gaOAuth2CredentialsJSON)) == 1 ? true : false;
        }

        /**
         * checks if the credentials that exist have expired (based on the token 'expiry_date')
         */
        private function CredentialsExpired()
        {
            $handle = fopen($this->gaOAuth2CredentialsJSON, 'r') or die('Cannot open file:  ' . $this->gaOAuth2CredentialsJSON . ' for reading');
            $data = fread($handle,filesize($this->gaOAuth2CredentialsJSON));
            fclose($handle);
            
            $credentials = json_decode($data, TRUE);
            $theTokenExpiryDate = $credentials['expiry_date'];
            $currentDate = date('d-m-Y H:i:s');

            return (strtotime($currentDate) > strtotime($theTokenExpiryDate)) ? true : false;
        }

        /**
         * reads in the JSON values from the credentials file and assigns to the instance
         */
        private function LoadExistingCredentials()
        {
            $handle = fopen($this->gaOAuth2CredentialsJSON, 'r') or die('Cannot open file:  ' . $this->gaOAuth2CredentialsJSON . ' for reading');
            $data = fread($handle,filesize($this->gaOAuth2CredentialsJSON));
            fclose($handle);

            $credentials = json_decode($data, TRUE);

            $this->gaAccessToken = $credentials['access_token'];
            $this->gaRefreshToken = $credentials['refresh_token'];
            $this->gaTokenExpiryDate = $credentials['expiry_date'];
        }

        /**
         * saves the current credentials to the JSON credentials file
         * overwriting anything already in there with the current values
         */
        private function SaveCredentials()
        {
            $handle = fopen($this->gaOAuth2CredentialsJSON, 'w') or die('Cannot open file:  ' . $this->gaOAuth2CredentialsJSON . ' for writing');
            $data = array(
                'access_token' => $this->gaAccessToken,
                'refresh_token' => $this->gaRefreshToken,
                'expiry_date' => $this->gaTokenExpiryDate
            );
            fwrite($handle, json_encode($data));
            fclose($handle);
        }

        /**
         * refreshes the tokens via the API and then updates the instance along
         * with updating the JSON credential file
         */
        private function RefreshAndStoreAndUseNewCredentials()
        {
            $gaTokenParameters = array(
                'refresh_token' => $this->gaRefreshToken,
                'client_id'     => $this->gaClientId,
                'client_secret' => $this->gaClientSecret,
                'grant_type'    => 'refresh_token'
            );

            $httpClient = new HttpClient();
            $responseJson = $httpClient->POST($this->gaTokenURLBase,$gaTokenParameters);
            $responseArray = json_decode($responseJson, TRUE); 

            $this->gaAccessToken = $responseArray['access_token'];
            $this->gaTokenExpiryDate = date('d-m-Y H:i:s', strtotime('+'.$responseArray['expires_in'].' seconds'));

            $this->SaveCredentials();
            unset($httpClient);
        }

        /**
         * In case of first run. gets user permission and generates the necassary
         * tokens, assigns then to the instance and then stores them in the JSON
         * credentials file.
         */
        private function AuthorizeAndStoreAndUseNewCredentials()
        {
            if (!isset($_GET['code'])) {
                $gaOAuth2URLParameters = array(
                  'client_id'       => $this->gaClientId,
                  'redirect_uri'    => $this->gaRedirectURI,
                  'scope'           => $this->gaAPIScope,
                  'response_type'   => 'code',
                  'approval_prompt' => 'force',
                  'access_type'     => 'offline'
                );
                $gaOAuth2URL = $this->gaAuthURLBase . '?' . http_build_query($gaOAuth2URLParameters);
                header('Location: ' . $gaOAuth2URL);
            }
            else if (isset($_GET['code']) && $_GET['code'] !== '') {
                $this->gaOAuth2Code = $_GET['code'];
                $gaAccessAndRefreshTokensParameters = array(
                    'client_id'     => $this->gaClientId,
                    'client_secret' => $this->gaClientSecret,
                    'grant_type'    => 'authorization_code',
                    'code'          => $this->gaOAuth2Code,
                    'redirect_uri'  => $this->gaRedirectURI
                );

                $httpClient = new HttpClient();
                $responseJson = $httpClient->POST(
                    $this->gaTokenURLBase,
                    $gaAccessAndRefreshTokensParameters);
                $responseArray = json_decode($responseJson, TRUE);

                $this->gaAccessToken = $responseArray['access_token'];
                $this->gaRefreshToken = $responseArray['refresh_token'];
                $this->gaTokenExpiryDate = date('d-m-Y H:i:s', strtotime('+'.$responseArray['expires_in'].' seconds'));
                
                $this->SaveCredentials();
                unset($httpClient);
                header('Location: ' . $this->gaRedirectURI);
            }
        }
    }
?>