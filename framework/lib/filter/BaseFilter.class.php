<?php
/**
 * Basic Filter
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
 * Class BaseFilter
 *
 * @category Framework
 * @package  Filter
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */
class BaseFilter extends Singleton {

    public function __construct() {
    }


    /**
     * Checks if a variable is empty
     *
     * @param mixed $variable
     * @return boolean
     */
    public function isEmpty($variable) {
        return (empty($variable));
    }

	/**
     * Checks if a variable is a string
     *
     * @param mixed $variable
     * @return boolean
     */
    public function isString($variable) {
        if (is_string($variable)!=null) {
            return true;
        }
        return false;
    }


    /**
     * Checks if a variable is an integer
     *
     * @param mixed $variable
     * @return boolean
     */
    public function isInteger($variable) {
        if (is_string($variable)) {
            if ($variable[0]=="0") {
                if (strlen($variable)>1) {
                    $variable = substr($variable,1);
                }
            }
        }
        return (filter_var($variable, FILTER_VALIDATE_INT));
    }


    /**
     * Checks if a variable is a float
     *
     * @param mixed $variable
     * @return boolean
     */
    public function isFloat($variable) {
        return (filter_var($variable,FILTER_VALIDATE_FLOAT));
    }

    /**
     * Checks if a variable is a boolean
     *
     * @param mixed $variable
     * @return boolean
     */
    public function isBoolean($variable) {
        return (filter_var($variable,FILTER_VALIDATE_BOOLEAN));
    }

    /**
     * Checks if a variable is null
     *
     * @param mixed $variable
     * @return boolean
     */
    public function isNull($variable) {
        if ($variable==null) {
            return true;
        }
        if ($variable=="null") {
            return true;
        }
        return false;
    }


    /**
     * Adds slashes to the variable
     *
     * @param  string $variable The variable
     * @return string
     */
    public function addSlashes($variable) {
        return addslashes((string) $variable);
    }


    /**
     * Strip the slashes from the variable
     *
     * @param string $variable The variable
     * @return string
     */
    public function stripSlashes($variable) {
        return stripslashes((string) $variable);
    }

    /**
     * Strips html tags from the variable
     *
     * @param string $variable The variable
     * @return string
     */
    public function stripTags($variable) {
        return strip_tags((string) $variable);
    }

    /**
     * Converts to htmlentities the variable
     *
     * @param mixed $variable The variable to convert
     * @return string
     */
    public function encodeHtml($variable) {
        return htmlentities($variable, ENT_QUOTES,'UTF-8');
    }

    /**
     * Converts to htmlspecialchars the variable
     *
     * @param mixed $variable The variable to convert
     * @return string
     */
    public function htmlSpecialChars($variable) {
       return htmlspecialchars($variable, ENT_QUOTES,'UTF-8');
    }


    /**
     * Decodes an html string
     *
     * @param $variable The variable to convert
     * @return string
     */
    public function decodeHtml($variable) {
        return html_entity_decode($variable, ENT_QUOTES,'UTF-8');
    }


    /**
     * Encodes a variable into UTF-8 charset
     *
     * @param $variable The variable to encode
     * @return string
     */
    public function encodeUTF8($variable) {
        return utf8_encode($variable);
    }

	/**
     * Decodes a variable into UTF-8 charset
     *
     * @param $variable The variable to decode
     * @return string
     */
    public function decodeUTF8($variable) {
        return utf8_decode($variable);
    }



    /**
     * Check if the variable is alphanumeric
     *
     * @param string $variable The variable to check
     * @return bool
     */
    public function isAlphaNumeric($variable) {
        $pattern = '/[^a-zA-Z0-9\s]/';
        preg_match($pattern,$variable,$matches);
        if (!empty($matches)) {
            return false;
        }
        return true;
    }


    /**
     * Checks if the variable is alpha
     *
     * @param string $variable The variable to check
     * @return bool
     */
    public function isAlpha($variable) {
        $pattern = '/[^a-zA-Z\s]/';
        preg_match($pattern,$variable,$matches);
        if (!empty($matches)) {
            return false;
        }
        return true;
    }


    /**
     * Checks if the variable is numeric
     *
     * @param $variable The variable to check
     * @return bool
     */
    public function isNumeric($variable) {
        return (is_numeric($variable));
    }

    /**
     * Checks if the parameter is a valid YYYY-MM-DD date
     *
     * @param $variable The variable to check
     * @return bool
     */
    public function isValidDate($variable) {
        $pattern = '/(19|20)(\d{2})-(\d{1,2})-(\d{1,2})/';
        $pattern2 = '/(19|20)(\d{2})\/(\d{1,2})\/(\d{1,2})/';
        preg_match($pattern,$variable,$matches);
        preg_match($pattern2,$variable,$matches2);
        if (!empty($matches)||!empty($matches2)) {
            return true;
        }
        return false;
    }

};
?>