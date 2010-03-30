<?php
class FWSoapClient {

    private $_client;
    private $_options;
    private $_library;
    private $_wsdl;

    function __construct($wsdl,$options=null)  {
        $config = Config::getInstance();
        $genconfig = $config->getGeneralConfig();
        $this->_library = (string) $genconfig->soapLibrary;
        $this->_options = $options;
        $this->_wsdl = "";
        $this->_wsdl = $wsdl;
        $this->_result = "";

        try {
            if ($this->_options!=null) {
                $this->_client = new SoapClient($this->_wsdl,$this->_options);
            }
            else {
                $this->_client = new SoapClient($this->_wsdl);
            }
        }
        catch (Exception $ex) {
            $this->_client = null;
            print $ex->getMessage();
        }

    }

	/**
  	* request a SOAP service function call. Fault, if occur, will be
  	* handle and translate to an ordinary error.
  	*
  	* @param $function
  	*   A string represent the function name.
  	*
  	* @param $params
  	*   An array of the function's parameters.
  	*
  	* @return
  	*   An array of the result with following keys:
  	*   - #error  : false, if no error. Otherwise, it is the error message
  	*   - #return : the return value from the service.
  	*/
    public function __call($function, $params = array())  {

        if ( empty($function) )    {
                throw new Exception('Function name is required');
        }

        $params = (array) $params;
        if ( $this->_client == null )  {
            throw new Exception('SOAP client object is not initialised');
        }
        if ( $this->_library == "php5soap" )   {
            try  {
                $result = $this->_client->__soapCall($function, $params);
                return $result;
            }
            catch(Exception $ex) {
                print $ex->getMessage();
            }

      if ( is_soap_fault($result['#return']) )    {
        throw new Exception ('Fault !code: !msg', array( '!code' => $result['#return']->faultcode, '!msg' => $result['#return']->faultstring ));
      }

    }

  }

};
?>