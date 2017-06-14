<?php
namespace multiparser;
use Yii;

class GetMultiContent extends Simple_html_dom{
    
    public $message = false;
    
    private $url;
    
    public $cookies_path = '';


    public function init($url) {
        
        $this->url = $url;
        
        if(!is_array($this->url)){
            $this->message("Not realized");
        }else{
            $this->message("initialization multiparser");
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
        
        $mh = curl_multi_init(); //создаем набор дескрипторов cURL
        
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
        }
        
        while (curl_multi_exec($mh, $running) == CURLM_CALL_MULTI_PERFORM); //Запускаем соединения
        usleep (100000);  //Задержка для избежания зацыкливания
        $status = curl_multi_exec($mh, $running);
        
        //Пока есть незавершенные соединения и нет ошибок мульти-cURL
        while ($running > 0 && $status == CURLM_OK) {
            curl_multi_select($mh, 4); //ждем активность на файловых дескрипторах. Таймаут 4сек
            usleep (500000);                 
            //Вдруг cURL хочет быть вызвана немедленно опять..
            while (($status = curl_multi_exec($mh, $running)) == CURLM_CALL_MULTI_PERFORM);
            //Если есть завершенные соединения
            while (($info = curl_multi_info_read($mh))!= false) {
                
                $easyHandle = $info['handle'];    //простой дескриптор cURL
                $result = curl_multi_getcontent($easyHandle);
                
                if (curl_errno($easyHandle) == 0) {    //если файл/страница успешно получена
                    
                    yield $this->load($result);
                    
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
            
        $contents = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg  = curl_error($ch);
      
        curl_close($ch);
        
        if($err == 0){
            return $this->load($contents);
        }else{
            $this->message($errmsg);
        } 
    }
    
}
