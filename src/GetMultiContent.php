<?php
namespace multiparser;

class GetMultiContent extends Simple_html_dom{
    
    public function init($url, $multi=true) {
        
        if(!$multi && !is_array($url)){
            echo "Not realized \n";
        }elseif(is_array($url)){
           $content = $this->arrayDescriptionInit($url);
        }else{
           $content = $this->sqlDescriptionInit($url);
        }
    }
            
    public function arrayDescriptionInit($url){
        echo "initialization function arrayDescriptionInit() \n";
        $mh = curl_multi_init(); //создаем набор дескрипторов cURL
        $descriptor = [];
        
        foreach ($url as $i=>$v){
            $ch[$i] = curl_init($v);
            curl_setopt($ch[$i], CURLOPT_HEADER, 0);          //Не включать заголовки в ответ
            curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);  //Убираем вывод данных в браузер
            curl_setopt($ch[$i], CURLOPT_CONNECTTIMEOUT, 30); //Таймаут соединения
            curl_multi_add_handle($mh, $ch[$i]);
            
            $descriptor[$i] = $ch[$i];
        }
        
        
    }
    
    public function sqlDescriptionInit($url){
        echo "initialization function sqlDescriptionInit() \n";
    }
}
