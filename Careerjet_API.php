<?php

 /** * Access Careerjet's job search from PHP
 *
 * PHP versions 4 and 5 (Updated for modern PHP)
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 *
 * @package     Careerjet_API
 * @author      Thomas Busch <api@careerjet.com>
 * @copyright   2007-2015 Careerjet Limited
 * @licence     PHP http://www.php.net/license/3_01.txt
 * @version     3.6
 * @link        http://www.careerjet.com/partners/api/php/
 */


 /**
 * Class to access Careerjet's job search API
 *
 * @package    Careerjet_API
 * @author     Thomas Busch <api@careerjet.com>
 * @copyright  2007-2015 Careerjet Limited
 * @link       http://www.careerjet.com/partners/api/php/
 */
class Careerjet_API {
  public $locale = '' ;
  public $version = '3.6';
  public $careerjet_api_content = '';

  /**
  * Creates client to Careerjet's API
  */

  public function __construct( $locale = 'en_GB' )
  {
    $this->locale = $locale;
  }

  /**
   * @ignore
   **/

  function call($fname , $args)
  {
    $url = 'http://public.api.careerjet.net/'.$fname.'?locale_code='.$this->locale;

    if (empty($args['affid'])) {
      return (object) array(
        'type' => 'ERROR',
        'error' => "Your Careerjet affiliate ID needs to be supplied. If you don't " .
                   "have one, open a free Careerjet partner account."
      );
    }

    foreach ($args as $key => $value) {
      $url .= '&'. $key . '='. urlencode($value);
    }

    if (empty($_SERVER['REMOTE_ADDR'])) {
      return (object) array(
        'type' => 'ERROR',
        'error' => 'not running within a http server'
      );
    }

    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
      $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = trim(array_shift(array_values(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']))));
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }

    $url .= '&user_ip=' . $ip;
    $url .= '&user_agent=' . urlencode($_SERVER['HTTP_USER_AGENT']);
    
    $current_page_url = '';
    if (!empty ($_SERVER["SERVER_NAME"]) && !empty ($_SERVER["REQUEST_URI"])) {
      $current_page_url = 'http';
      if (!empty ($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
        $current_page_url .= "s";
      }
      $current_page_url .= "://";
  
      if (!empty ($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80") {
        $current_page_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
      } else {
        $current_page_url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
      }
    }

    $header = "User-Agent: careerjet-api-client-v" . $this->version . "-php-v" . phpversion();
    if ($current_page_url) {
      $header .= "\nReferer: " . $current_page_url;
    }

    $careerjet_api_context = stream_context_create(array(
      'http' => array('header' => $header)
    ));

    $response = file_get_contents($url, false, $careerjet_api_context);
    return json_decode($response);
  }
  
  
  /**
   * Performs a search using Careerjet's public search API
   *
   * @param   array  $args map of search parameters
   * @return object(stdClass)  An object containing results
   */
  function search($args)
  {
    $result =  $this->call('search' , $args);
    if (isset($result->type) && $result->type == 'ERROR') {
      trigger_error( $result->error );
    }
    return $result;
  }
}

?>
