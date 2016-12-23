<?php

namespace Travelport\Air;


class Service
{
    private $soap_do;
    private $user;
    private $password;

    public function __construct($url, $user, $password)
    {
        $this->soap_do = curl_init($url);
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Make a soap request.
     *
     * @param string $message
     * @param array  $headers
     *
     * @return string XML string
     */
    public function call($message, $headers = [])
    {
        $this->setCURL($message, array_merge([
            "Content-Type: text/xml;charset=UTF-8",
            "Accept: gzip,deflate",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"\"",
            "Authorization: Basic " . base64_encode($this->user.':'.$this->password),
            "Content-length: " . strlen($message)
        ], $headers));

         return curl_exec($this->soap_do);
    }

    /**
     * Set the required curl options.
     *
     * @param string $message
     * @param array  $headers
     */
    protected function setCURL($message, array $headers)
    {
        curl_setopt($this->soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->soap_do, CURLOPT_POST, true );
        curl_setopt($this->soap_do, CURLOPT_POSTFIELDS, $message);
        curl_setopt($this->soap_do, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->soap_do, CURLOPT_RETURNTRANSFER, true);
    }
}