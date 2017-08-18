<?php
namespace multiparser;
use Yii;

class GetMultiContent extends Simple_html_dom{
    
    /**
     *
     * @var type strig
     * сообщения отображающие процесс работы парсера
     */
    public $message = false;
    /**
     *
     * @var type string
     * URL парсируемых сайтов
     */
    private $url;
    /**
     *
     * @var type string
     * путь к файлу записи coocies 
     */
    public $cookies_path = '';
    /**
     *
     * @var type string
     * URL возвращаесого дескриптора
     */
    public $current_url = null;
    
    /**
     * не ипользовать объект Simple_html_dom для обработки контента
     */
    public $no_parser = false;
    
    /**
     * Номер ошибки ответа CURL
     * @var type int
     */
    public $err;
    

    public function init($url, $no_parser = false) {
        
        $this->url = $url;
        $this->no_parser = $no_parser;
        
        if(!is_array($this->url)){
            $this->message("initialization init_curl");
            return $this->init_curl();
        }else{
            $this->message("initialization init_multi_curl");
            return $this->init_multi_curl();
        }
    }
    
    public function message($message) {
        
        if($this->message){
            echo $message. "\n";
        }else{
            return null;
        }
    }
    
    public function init_multi_curl() {
        
        $mh = curl_multi_init(); 
        $descriptor = [];
        
        foreach ($this->url as $i=>$v){
            $ch[$i] = curl_init($v);
            curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);   
            curl_setopt($ch[$i], CURLOPT_HEADER, 0); 
            curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch[$i], CURLOPT_ENCODING, "deflate"); 
            curl_setopt($ch[$i], CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');
            curl_setopt($ch[$i], CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch[$i], CURLOPT_TIMEOUT, 30);
            curl_setopt($ch[$i], CURLOPT_MAXREDIRS, 10); 
            curl_setopt($ch[$i], CURLOPT_REFERER, 'https://www.google.com.ua/');
            curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST,0);
            curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER,0);
            curl_setopt($ch[$i], CURLOPT_COOKIEJAR, Yii::getAlias($this->cookies_path));  
            curl_setopt($ch[$i], CURLOPT_COOKIEFILE, Yii::getAlias($this->cookies_path)); 
            curl_multi_add_handle($mh, $ch[$i]);
            $descriptor[$v] = $ch[$i];
        }
        
        while (curl_multi_exec($mh, $running) == CURLM_CALL_MULTI_PERFORM); 
        usleep (100000);  
        $status = curl_multi_exec($mh, $running);
        
        
        while ($running > 0 && $status == CURLM_OK) {
            curl_multi_select($mh, 4); 
            usleep (500000);                 
            
            while (($status = curl_multi_exec($mh, $running)) == CURLM_CALL_MULTI_PERFORM);
            
            while (($info = curl_multi_info_read($mh))!= false) {
                
                $easyHandle = $info['handle'];    
                $result = curl_multi_getcontent($easyHandle);
                $this->err = curl_errno($easyHandle);
                
                if (curl_errno($easyHandle) == 0) {    
                    
                    $this->message("response descriptor $easyHandle");
                    foreach($descriptor as $key => $desc){
                        if($desc == $easyHandle){
                            $this->info = $key;
                        }
                    }
                    
                    ($this->no_parser) ? yield ($result) : yield ($this->load($result));
                    
                }else{
                    $this->message(curl_error($easyHandle));
                }
                curl_multi_remove_handle($mh, $easyHandle);
                curl_close($easyHandle);
            }
        }
    }
    
    public function init_curl() {
        
        $ch = curl_init($this->url);
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   
            curl_setopt($ch, CURLOPT_HEADER, 0); 
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_ENCODING, "deflate"); 
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10); 
            curl_setopt($ch, CURLOPT_REFERER, 'https://www.google.com.ua/');
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
            curl_setopt($ch, CURLOPT_COOKIEJAR, Yii::getAlias($this->cookies_path));  
            curl_setopt($ch, CURLOPT_COOKIEFILE, Yii::getAlias($this->cookies_path));
            
        $contents = curl_exec($ch);
        $this->err = curl_errno($ch);
        $errmsg  = curl_error($ch);
      
        curl_close($ch);
        
        if($this->err == 0){
            return ($this->no_parser) ? $contents : $this->load($contents);
        }else{
            $this->message($errmsg);
            return FALSE;
        } 
    }
    
}
