<?php
/**
 * customized 2020-11-28 14:14
 */
namespace App\Component;

use \App\Traits\LogTrait as Log;

class CurlComponent
{
    use Log;

    //gestor de errores
    private $isError = FALSE;
    private $arErrors = [];

    private $arOptions;
    private $arGet;
    private $arPost;
    private $sUrlPost;
    private $sUrlGetCurl;
    private $sUrlRedirect;

    //respuesta y mensaje asociado
    private $sResponse;
    private $sMessage;

    //datos conexión ws
    private $sUrlGet;
    private $isLogDebug;

    public function __construct($sUrlGet="")
    {
        $this->sUrlGet = $sUrlGet;
        $this->arOptions = [];
        $this->arGet = [];
        $this->arPost = [];

        if(!$this->is_curlinstalled())
            $this->add_error("__construct: curl not installed");
    }//__construct

    private function is_curlinstalled(){return in_array("curl",get_loaded_extensions());}

    private function load_urlpost($isCleanPost=0)
    {
        if($this->arPost)
        {
            if($isCleanPost)
            {
                $this->sUrlPost = http_build_query($this->arPost);
            }
            else
            {
                $arParams = [];
                foreach($this->arPost as $k=>$v)
                    $arParams[] = "$k=$v";
                $this->sUrlPost = implode("&",$arParams);
            }
        }
        //bug($this->sUrlPost,"load_urlpost");
    }//load_urlpost

    private function load_curl($isClean=0)
    {
        $this->sUrlGetCurl = $this->sUrlGet;
        if($this->arGet)
        {
            if($isClean)
            {
                $this->sUrlGetCurl .= "?".http_build_query($this->arGet);
            }
            else
            {
                $sUrl="?";
                $arParams = [];
                foreach($this->arGet as $k=>$v)
                    $arParams[] = "$k=$v";
                $sUrl.= implode("&",$arParams);
            }
            $this->sUrlGetCurl .= $sUrl;
        }
    }//load_curl

    private function _curl_execute($arOptions,$sMethod="")
    {
        $this->logd($arOptions,"curl._curl_execute.options");
        $oCurl = curl_init();
        curl_setopt_array($oCurl,$arOptions);
        $this->sResponse = curl_exec($oCurl);
        curl_close($oCurl);
        if($this->sResponse===FALSE)
            $this->add_error("$sMethod.Error Curl.request()");
    }//_curl_execute

    public function request()
    {
        try
        {
            $arOptions = array
            (
                CURLOPT_URL => $this->sUrlGetCurl,

                CURLOPT_ENCODING => "UTF-8",//comunicacion en utf8
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => 1,//return web page
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_TIMEOUT => 60,//segundos
                CURLOPT_VERBOSE => 0
            );

            if($this->arOptions)
                $arOptions = $this->arOptions;

            $this->_curl_execute($arOptions,"request");
            $this->log_attribs("request");
        }
        catch(Exception $oEx)
        {
            $this->add_error("exception.request: {$oEx->getMessage()}");
        }
    }//request

    public function request_get($isCleanGet=1)
    {
        try
        {
            //1: limpia la url get
            $this->load_curl($isCleanGet);

            if($this->sUrlGetCurl && $this->arGet)
            {
                $arOptions = array
                (
                    CURLOPT_ENCODING => "UTF-8",     // comunicacion en utf8
                    CURLOPT_URL => $this->sUrlGetCurl,
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_RETURNTRANSFER => 1,// return web page
                    CURLOPT_CONNECTTIMEOUT => 3,
                    CURLOPT_TIMEOUT => 60,//segundos
                    CURLOPT_VERBOSE => 0
                );
                //Devuelve TRUE en caso de éxito o FALSE en caso de error. Sin embargo,
                //si la opción CURLOPT_RETURNTRANSFER está establecida, devolverá el
                //resultado si se realizó con éxito, o FALSE si falló.
                $this->_curl_execute($arOptions,"request_get");
                $this->log_attribs("request_get");
            }
            else
            {
                $this->add_error("request_get: no urlcurl:$this->sUrlGetCurl or no this->arGet");
            }
        }
        catch(Exception $oEx)
        {
            $this->add_error("exception.post: {$oEx->getMessage()}");
        }
        return $this;
    }//request_get

    public function request_fileget($isCleanGet=1)
    {
        try
        {
            //1: limpia la url get
            $this->load_curl($isCleanGet);

            if($this->sUrlGetCurl)
            {
                $this->sResponse = file_get_contents($this->sUrlGetCurl);
                if($this->sResponse===FALSE)
                    $this->add_error("Error en request_fileget");
            }
            else
                $this->add_error("request_fileget: no urlcurl:$this->sUrlGetCurl");
        }
        catch(Exception $oEx)
        {
            $this->add_error("exception.request_fileget: {$oEx->getMessage()}");
        }
    }//request_fileget

    public function request_post($isCleanPost=1)
    {
        try
        {
            $this->load_curl($isCleanPost);
            $this->load_urlpost($isCleanPost);

            if($this->sUrlGetCurl && $this->sUrlPost)
            {
                $arOptions = array
                (
                    CURLOPT_URL => $this->sUrlGetCurl,
                    CURLOPT_POSTFIELDS => $this->sUrlPost,

                    CURLOPT_ENCODING => "UTF-8",     // comunicacion en utf8
                    CURLOPT_RETURNTRANSFER => 1,     // return web page
                    CURLOPT_HEADER         => 0,     // don't return headers
                    CURLOPT_FOLLOWLOCATION => 1,     // follow redirects
                    CURLOPT_AUTOREFERER    => 0,     // set referer on redirect
                    CURLOPT_CONNECTTIMEOUT => 120,   // timeout on connect
                    CURLOPT_TIMEOUT        => 120,   // timeout on response
                    CURLOPT_MAXREDIRS      => 10,    // stop after 10 redirects
                );

                $this->_curl_execute($arOptions,"request_post");
                $this->log_attribs("request_post");
            }
            else
            {
                $this->add_error("request_post: no urlcurl:$this->sUrlGetCurl");
                $this->add_error("request_post: no urlget: $this->sUrlPost");
            }
        }
        catch(Exception $oEx)
        {
            $this->add_error("exception.request_post: {$oEx->getMessage()}");
        }
    }//request_post

    public function request_postrd()
    {
        try
        {
            $this->load_curl();
            $this->load_urlpost();

            if($this->sUrlGetCurl && $this->sUrlPost)
            {
                //http://stackoverflow.com/questions/17026411/curl-get-url-from-redirect
                $arOptions = array
                (
                    CURLOPT_URL => $this->sUrlGetCurl,
                    CURLOPT_POSTFIELDS => $this->sUrlPost,

                    CURLOPT_ENCODING => "UTF-8",     // comunicacion en utf8
                    CURLOPT_HEADER         => 1,     // don't return headers 1: obtiene la url de redireccion
                    CURLOPT_NOBODY => 0,
                    CURLOPT_RETURNTRANSFER => 1,     // return web page
                    CURLOPT_FOLLOWLOCATION => 0,     // follow redirects
                    CURLOPT_AUTOREFERER    => 0,     // set referer on redirect
                    CURLOPT_CONNECTTIMEOUT => 120,   // timeout on connect
                    CURLOPT_TIMEOUT        => 120,   // timeout on response
                    CURLOPT_MAXREDIRS      => 10,    // stop after 10 redirects
                );

                $this->_curl_execute($arOptions,"request_postrd");
                $this->log_attribs("request_postrd");
                //pr($this->sResponse);die;
                //m (PCRE_MULTILINE) trata el string multilinea como una sola linea
                //i (PCRE_CASELESS) las letras en el patrón coincidirán tanto con letras mayúsculas como minúsculas.
                preg_match_all("/^Location:(.*)$/mi",$this->sResponse,$arLocation);

                $this->sUrlRedirect = $arLocation[1][0];
                if(!$this->sUrlRedirect) $this->add_error("request_postrd: no urlredirect received");

            }
            else
            {
                $this->add_error("request_postrd: no urlcurl:$this->sUrlGetCurl");
                $this->add_error("request_postrd: no urlget: $this->sUrlPost");
            }
        }
        catch(Exception $oEx)
        {
            $this->add_error("exception.post: {$oEx->getMessage()}");
        }
    }//request_postrd

    public function get_action_fields()
    {
        $arResult = [];
        $arMatch = [];

        $arText = explode("<",$this->sResponse);
        //pr($arText);
        foreach($arText as $sText)
        {
            if(strstr($sText,"action"))
            {
                preg_match("/action=[\"|'](.*?)['|\"]/",$sText,$arMatch);
                $sKey = $arMatch[1];
                $arR = array("action"=>$sKey);
            }
            //fields
            else
            {
                //<input
                preg_match("/name=[\"|'](.*?)['|\"]/",$sText,$arMatch);
                if($arMatch)
                {
                    $sKey = $arMatch[1];

                    preg_match("/value=[\"|'](.*?)['|\"]/",$sText,$arMatch);
                    $sVal = $arMatch[1];

                    $arR = array($sKey=>$sVal);
                }
            }

            if($sKey)
                $arResult[] = $arR;
        }

        $arMatch = [];
        foreach($arResult as $arKV)
        {
            $sKey = array_keys($arKV);
            $sKey = $sKey[0];
            $arMatch[$sKey] = $arKV[$sKey];
        }
        //$this->log_attribs("AppComponentCurl.get_action_fields");//@@COMMET
        return $arMatch;
    }//get_action_fields

    private function log_attribs($sTitle=NULL)
    {
        if($sTitle) $this->logd($sTitle);
        $this->logd($this->sUrlGetCurl,"get");
        $this->logd($this->sUrlPost,"post");
        $this->logd($this->sResponse,"response");
        $this->logd($this->arOptions,"options");
    }//log_attribs()

    public function show_response(){echo $this->sResponse;}

    /**
     * Resetea $arReference y aplica el/los valores pasados en $mxValue
     * Los falsi values se omitirán: NULL,0,"0",FALSE,""
     * @param array $arReference Array a modificar
     * @param string|csvstring|array $mxValue valor o valores a asignar
     */
    private function set_array(&$arReference,$mxValue)
    {
        $arReference = [];
        if($mxValue!==NULL)
        {
            if(is_array($mxValue))
                $arReference = $mxValue;
            elseif(strstr($mxValue,","))
                $arReference = explode(",",$arReference);
            elseif($mxValue)
                $arReference[] = $mxValue;
        }
    }//set_array

    //GETS
    public function is_error(){return $this->isError;}
    public function get_errormsg(){return implode(",",$this->arErrors);}
    public function get_statusmsg(){return $this->sMessage;}
    public function get_response(){return $this->sResponse;}
    public function get_urlpost(){return $this->sUrlPost;}
    public function get_urlget_final(){return $this->sUrlGetCurl;}
    public function get_urlredir(){return $this->sUrlRedirect;}

    //SETS
    public function is_logdebug($isOn=TRUE){$this->isLogDebug=$isOn;}
    public function add_option($sOption,$sValue){$this->arOptions[$sOption]=$sValue; return $this;}
    public function set_options($arOptions){$this->arOptions=$arOptions; return $this;}

    public function add_post($sKey,$sValue){$this->arPost[$sKey]=$sValue; return $this;}
    public function set_post($mxValue){$this->set_array($this->arPost,$mxValue); return $this;}

    public function add_get($sKey,$sValue){$this->arGet[$sKey]=$sValue; return $this;}
    public function set_get($mxValue){$this->set_array($this->arGet,$mxValue); return $this;}

    protected function add_error($sMessage){$this->isError=TRUE;$this->arErrors[]=$sMessage;
        $this->log($sMessage,"Curl.error");}
    protected function add_status($sResponse,$sMessage){$this->sResponse=$sResponse;$this->sMessage=$sMessage;}

    public function set_urlget_single($sUrl){$this->sUrlGet=$sUrl; return $this;}
    public function set_urlget_final($sUrl){$this->sUrlGetCurl=$sUrl; return $this;}
}