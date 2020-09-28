<?php

namespace CheckMail;

use Exception;

/**
 * PHP 7
 *
 * Copyright 2020, Anton Volokha <antonvolokha@gmail.com>
 *
 */

class CheckMailApi {
  /**
 * API endpoint url
 *
 * @var string
 */
  private $endpoint = 'https://mailcheck.p.rapidapi.com/';

/**
 * API access key
 *
 * - null
 *
 * @var string
 */
  private $apiKey = '';

/**
 * Write additional logs
 *
 */
  private $debug = false;

/**
 * Constructor set API access key
 *
 * @param string $key.
 */
  public function __construct($key, $debug = false) {
    $this->apiKey = $key;
    $this->debug = $debug;
  }

  public function checkEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
      throw new CheckMailApiException('Invalid email');

    $domain = $this->_getDomain($email);
    return $this->check($domain);
  }

  public function checkDomain($domain) {
    return $this->check($domain);
  }

  private function check($domain) {
    $curl = curl_init();

    $url = sprintf($this->endpoint . '?disable_test_connection=false&domain=%s', $domain);

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'x-rapidapi-host: mailcheck.p.rapidapi.com',
        'x-rapidapi-key: ' . $this->apiKey
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err)
      return array('error' => $err);

    if ($this->debug)
      var_dump($response);

    $response = json_decode($response, true);
    if (empty($response))
      throw new CheckMailApiException('Response is empty');

    if (!empty($response['message']))
      throw new CheckMailApiException($response['message']);

    return $response;
  }

  /**
   * Helper function to get email domain
   *
   * @var str username - email
   * @return str domain
   */
  private function _getDomain($email) {
    $email = explode('@', $email);
    if (count($email) < 2)
      return null;

    return trim($email[1]);
  }
}

class CheckMailApiException extends Exception {}