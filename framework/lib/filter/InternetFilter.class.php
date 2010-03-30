<?php
/**
 * Basic "Internet" Filter
 *
 * PHP Version 5.2
 *
 * @category Framework
 * @package  Filter
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/**
 * Class InternetFilter
 *
 * @category Framework
 * @package  Filter
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */
class InternetFilter {

 /**
     * Checks if a variable is a valid email
     *
     * @param mixed $variable
     * @return boolean
     */
    public function isValidEmail($variable) {
        if (filter_var($variable,FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        return false;
    }


	/**
     * Checks if a variable is a valid url
     *
     * @param mixed $variable
     * @return boolean
     */
    public function isValidURL($variable) {
        if (filter_var($variable,FILTER_VALIDATE_URL)) {
            return true;
        }
        return false;
    }


    /**
     * Checks if variable is a valid IPV4 IP Address
     *
     * @param $variable
     * @return boolean
     */
    public function isValidIPV4($variable) {
        $pattern = '/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/';
        if (preg_match($pattern,$variable,$matches)) {
            return true;
        }
        else {
            return false;
        }
    }


    /**
     * Checks for a valid email and valid email domain
     * @param $email string The email to check
     * @return bool
     */
    public function isValidEmailDomain($email) {

        if(preg_match('/^\w[-.\w]*@(\w[-._\w]*\.[a-zA-Z]{2,}.*)$/', $email, $matches))  {

            if(function_exists('checkdnsrr')) {
                if(checkdnsrr($matches[1] . '.', 'MX')) {
                    return true;
                }
                if(checkdnsrr($matches[1] . '.', 'A')) {
                    return true;
                }
            }
        }
        else{
            return true;
        }
        return false;
    }


};
?>