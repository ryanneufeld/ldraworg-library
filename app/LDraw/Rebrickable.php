<?php

namespace App\LDraw;

class Rebrickable
{
    
    /**
     * $retries
     *
     * @var int
     */
    public int $retries = 2;

    /**
     * $rate_limit
     *
     * @var int
     */
    public int $rate_limit = 1;

    protected int $last_call = 1;

    public function __construct(
        public readonly string $api_key,
        public readonly string $api_url
    ) {}

    /**
     * responseCallDelay
     *
     * @return void
     */
    private function responseCallDelay(): void {
        $time_since_last = time() - $this->last_call;
        if ($time_since_last < $this->rate_limit) {
            sleep($this->rate_limit - $time_since_last);
        }
    }  
    
    /**
     * makeApiCall
     *
     * @param string $url
     * 
     * @return array|bool
     */
    private function makeApiCall(string $url): array|bool
    {
        $request_headers = [];
        $request_headers[] = "Authorization: key {$this->api_key}";
        $request_headers[] = 'Accept: application/json';
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
    
        $this->responseCallDelay();
        
        $response = curl_exec($ch);
        $this->last_call = time();
    
        if ($response === false) {
            $result = false;
        } else {
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $result = ['code' => $code, 'response' => json_decode($response, true)];
        }
        curl_close($ch);
        return $result;
    }

    private function getResponse(string $url): array 
    {
        $response = $this->makeAPICall($url);
        if ($response === false or $response['code'] == '429') {
          for ($i=0; $i < $this->retries; $i++) {
            if ($response['code'] == '429') {
              $detail = explode(' ', $response['response']['detail']);
              $this->rate_limit = trim($detail[count($detail)-2]);
              if (!is_numeric($this->rate_limit)) {
                $this->rate_limit = 10;
              }
            }
            else {
              $this->rate_limit = 1;
            }
            $response = $this->makeAPICall($url);
            if ($response !== false and $response['code'] != '429') break;
          }  
        }
        if ($response === false) {
          return ['error' => "API call failed with unspecified error"];
        }
        elseif ($response['code'] != '200') {
          return ['error' => "API call failed with code {$response['code']}"];
        }
        else {
          return $response['response'];
        }  
      }
      
      public function getSetParts(string $setnumber) {
        $parts = [];
        $url = "{$this->api_url}/sets/{$setnumber}/parts/?inc_minifig_parts=1";
        do {
          $response = $this->getResponse($url);
          if (isset($response['error'])) return $response;
          $url = $response['next'];
          if ($response['count'] > 0) $parts = array_merge($parts, $response['results']);
        } while ($url !== NULL);
        return $parts;
      }
    
      public function getSet($setnumber) {
        $url = "{$this->api_url}/sets/{$setnumber}/";
        return $this->getResponse($url);
      }  
    
      public function getLdrawNum($rb_num) {
        $url = "{$this->api_url}/parts/$rb_num/";
        $rb_part = $this->getResponse($url);
        if (!isset($rb_part['error']) && isset($rb_part['external_ids']['LDraw'][0])) {
          return $rb_part['external_ids']['LDraw'][0];
        }
        else {
          return false;
        }
      }  
    
}