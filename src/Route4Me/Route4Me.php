<?php
namespace Route4Me;

use Route4Me\Exception\ApiError;
use Route4Me\Exception\myErrorHandler;
use Route4Me\Enum\Endpoint;

class Route4Me
{
    static public $apiKey;
    static public $baseUrl = Endpoint::BASE_URL;

    public static function setApiKey($apiKey)
    {
        self::$apiKey = $apiKey;
    }

    public static function getApiKey()
    {
        return self::$apiKey;
    }

    public static function setBaseUrl($baseUrl)
    {
        self::$baseUrl = $baseUrl;
    }

    public static function getBaseUrl()
    {
        return self::$baseUrl;
    }
    
    public static function fileUploadRequest($options) {
        $query = isset($options['query']) ? array_filter($options['query']) : array();

        if (sizeof($query)==0) {
            return null;
            
        }
        
        $body = isset($options['body']) ? array_filter($options['body']) : null;
            
        $fname = isset($body['strFilename']) ? $body['strFilename'] : '';
        
        if ($fname=='') {
            return null;  
        } 

        $rpath = function_exists('curl_file_create') ? curl_file_create(realpath($fname)) : '@'.realpath($fname);
        
        $url = self::$baseUrl.$options['url'].'?'.http_build_query(array_merge(array('api_key' => self::getApiKey()), $query));
        
        $ch = curl_init($url);
        
        $curlOpts = array(
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE
        );
        
        curl_setopt_array($ch, $curlOpts);
        
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: multipart/form-data",
            'Content-Disposition: form-data; name="strFilename"'
        ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('strFilename' => $rpath)); 
        
        $result = curl_exec($ch);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($result, true);
        
        if (200==$code) {
            return $json;
        } elseif (isset($json['errors'])) {
            throw new ApiError(implode(', ', $json['errors']));
        } else {
            throw new ApiError('Something wrong');
        }
    }

    public static function makeRequst($options) {
        $errorHandler = new myErrorHandler();
        
        $old_error_handler = set_error_handler(array($errorHandler, "proc_error"));
        
        $method = isset($options['method']) ? $options['method'] : 'GET';
        $query = isset($options['query']) ? array_filter($options['query'], function($x) { return !is_null($x); } ) : array();

        $body = isset($options['body']) ? $options['body'] : null;
        $file = isset($options['FILE']) ? $options['FILE'] : null;
        $headers = array(
            "User-Agent: Route4Me php-sdk"
        );
        
        if (isset($options['HTTPHEADER'])) {
            $headers[] = $options['HTTPHEADER'];
        }
         
        if (isset($options['HTTPHEADERS'])) {
            foreach ($options['HTTPHEADERS'] As $header) {
                $headers[] = $header;
            } 
        }

        $ch = curl_init();
        
        $url = $options['url'].'?'.http_build_query(array_merge(
            $query, array('api_key' => self::getApiKey())
        ));

        $baseUrl = self::getBaseUrl();

        $curlOpts = arraY(
            CURLOPT_URL            => $baseUrl.$url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 80,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_HTTPHEADER     => $headers
        );
        
        curl_setopt_array($ch, $curlOpts);
        
        if ($file!=null) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            $fp=fopen($file, 'r');
            curl_setopt($ch, CURLOPT_INFILE, $fp);
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file));
        }

        switch ($method) {
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); 
            break;
        case 'DELETEARRAY':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query));
            break;
        case 'PUT':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            break;
        case 'POST':
           if (isset($body)) {
                $bodyData = json_encode($body);
               if (isset($options['HTTPHEADER'])) {
                  if (strpos($options['HTTPHEADER'], "multipart/form-data")>0) {
                      $bodyData = $body;
                  }
               }
               
               curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyData); 
            } 
            break;
        case 'ADD':
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query)); break;
        }

        if (is_numeric(array_search($method, array('DELETE', 'PUT')))) {
            if (isset($body)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body)); 
            } 
        }

        $result = curl_exec($ch);

        $isxml = FALSE;
        $jxml = "";
        if (strpos($result, '<?xml')>-1) {
            $xml = simplexml_load_string($result);
            //$jxml = json_encode($xml);
            $jxml = self::object2array($xml);
            $isxml = TRUE;
        }
        
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if (200==$code) {
            if ($isxml) {
                $json = $jxml;
            } else {
                $json = json_decode($result, true);
            }
            
            if (isset($json['errors'])) {
                throw new ApiError(implode(', ', $json['errors']));
            } else {
                return $json;
            }
        }  elseif (409==$code) {
            throw new ApiError('Wrong API key');
        } else {
            throw new ApiError('Something wrong');
        }
    }

    /**
     * @param $object: JSON object
     */
    public static function object2array($object)
    {
        return @json_decode(@json_encode($object), 1);
    }

    public static function makeUrlRequst($url, $options) {
        $method = isset($options['method']) ? $options['method'] : 'GET';
        $query = isset($options['query']) ?
            array_filter($options['query'], function($x) { return !is_null($x); } ) : array();
        $body = isset($options['body']) ? $options['body'] : null;
        $ch = curl_init();
        
        $curlOpts = arraY(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_HTTPHEADER     => array(
                'User-Agent' => 'Route4Me php-sdk'
            )
        );
        
        curl_setopt_array($ch, $curlOpts);
        
        switch ($method) {
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); 
            break;
        case 'DELETEARRAY':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query));
            break;
        case 'PUT':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            break;
        case 'POST':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
            break;
        case 'ADD':
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query)); break;
        }
        
        if (is_numeric(array_search($method, array('DELETE', 'PUT', 'POST')))) {
            if (isset($body)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body)); 
            } 
        }

        $result = curl_exec($ch);
        
        $isxml = FALSE;
        $jxml = "";
        
        if (strpos($result, '<?xml')>-1) {
            $xml = simplexml_load_string($result);
            $jxml = json_encode($xml);
            $isxml = TRUE;
        }
        
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($isxml) {
            $json = $jxml;
        } else {
            $json = json_decode($result, true);
        }
        
        if (200==$code) {
            return $json;
        } elseif (isset($json['errors'])) {
            throw new ApiError(implode(', ', $json['errors']));
        } else {
            throw new ApiError('Something wrong');
        }
    }
    
    /**
     * Prints on the screen main keys and values of the array 
     * @param $results: object to be printed on the screen.
     * @param $deepPrinting: if true, object will be printed recursively.
     */
    public static function simplePrint($results, $deepPrinting = null)
    {
        if (isset($results)) {
            if (is_array($results)) {
                foreach ($results as $key=>$result) {
                    if (is_array($result)) {
                        foreach ($result as $key1=>$result1) {
                            if (is_array($result1)) {
                                  if ($deepPrinting) {
                                      echo "<br>$key1 ------><br>";
                                      Route4Me::simplePrint($result1, true);
                                      echo "------<br>";
                                  } else {
                                      echo $key1." --> "."Array() <br>";
                                  } 
                            } else {
                                if (is_object($result1)) {
                                    if ($deepPrinting) {
                                        echo "<br>$key1 ------><br>";
                                        $oarray = (array)$result1;
                                        Route4Me::simplePrint($oarray, true);
                                        echo "------<br>";
                                    } else {
                                        echo $key1." --> "."Object <br>";
                                    } 
                                } else {
                                    if (!is_null($result1)) {
                                        echo $key1." --> ".$result1."<br>"; 
                                    }   
                                }
                            }
                        }
                    } else {
                        if (is_object($result)) {
                            if ($deepPrinting) {
                                echo "<br>$key ------><br>";
                                $oarray = (array)$result;
                                Route4Me::simplePrint($oarray, true);
                                echo "------<br>";
                            } else {
                                echo $key." --> "."Object <br>";
                            } 
                        } else {
                            if (!is_null($result)) {
                                echo $key." --> ".$result."<br>";
                            }
                        }
                        
                    }
                    //echo "<br>";
                }
            } 
        }
    }
}
