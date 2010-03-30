<?php
/**
 * Locale i18n handler
 *
 * PHP Version 5.2
 *
 * @package  Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */

/**
 * Class i18n
 *
 * @package  Framework
 * @author   Andrés Ignacio Martínez Soto <anmarso4@fiv.upv.es>
 * @license  BSD Style
 * @link     http://www.andresmartinezsoto.es
 *
 */
class i18n extends Singleton {

   /**
    * The Config object
    *
    * @var Config
    */
   private static $_config;

   /**
    * The path of the Locales
    *
    * @var string
    */
   private static $_localeDir;


   /**
    * Property to hold if i18n has already init
    * @var bool
    */
   private static $_init = false;

   /**
    * Inits the i18n Object
    * @return void
    */
   public static function init() {
      self::$_config = Config::getInstance();
      self::$_localeDir = getcwd().DS."framework".DS."locales";
      self::$_init = true;
   }


   /**
    * Sets a locale to the framework
    *
    * @param $locale string Language code
    * @return void
    */
   public static function setLocale($locale=null) {

       if (!self::$_init) {
           self::init();
       }

       switch ($locale) {
         case "es":
            $locale = 'es_ES';
         break;

         case "ca":
            $locale = 'ca_ES';
         break;

         case "en":
            $locale = 'en_GB';
         break;

         case "de":
             $locale = "de_DE";
         break;

         case "fr":
             $locale = "fr_FR";
         break;

         default:
             if (Session::issetData("currentLocale","locale")) {
                 $locale = Session::get("currentLocale","locale");
             }
             else {
                 $locale = self::$_config->getParam("defaultLocale");
             }
         break;
      };

      Session::set("currentLocale",$locale,"locale");
      $localeHack = self::$_config->getParam("localeHack");
      if ($localeHack!=null) {
          $locale = "{$locale}{$localeHack}";
      }
      $env = "LC_ALL={$locale}";
      putenv($env);
      setlocale(LC_ALL,$locale);

      bindtextdomain("messages",self::$_localeDir);
      textdomain("messages");
   }

   /**
    * Translates a string to the current language
    *
    * @param $string string The string to be translated
    * @return string
    */
   public function translate($string) {
      $translation = gettext($string);
      return $translation;
   }
}
?>