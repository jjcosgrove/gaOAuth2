<?php
    /**
     * HttpClient: minimal helper class for HTTP GET/POST
     *
     * @author Jonathan James Cosgrove
     * @version 1.0.0
     * @link https://github.com/jjcosgrove/gaOAuth2
     */
    class HttpClient{
        public function __construct()
        {
        }
        /**
         * perform a GET request
         *
         * @param [string] $url    the url of the endpoint/api
         * @param [array]  $header HTTP header
         */
        public function GET($url, $header = array())
        {
            $ch = curl_init();
            $options = array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_POST => FALSE
            );
            curl_setopt_array($ch, $options);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        }
        /**
         * perform a POST request
         *
         * @param string $url        the url of the endpoint/api
         * @param [array] $postFields the fields to send with the POST request
         */
        public function POST($url, $postFields)
        {
            $ch = curl_init();
            $options = array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_POST => TRUE,
                CURLOPT_POSTFIELDS => $postFields
            );
            curl_setopt_array($ch, $options);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        }
    }
?>
