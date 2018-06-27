<?php

    /*установка пункта меню активным если в нем находимся*/
    function check_active($url){
        $real_uri = $_SERVER['REQUEST_URI'];
        if (($p = strpos($real_uri, '?')) === false){
            $uri = substr($real_uri, 1);
        }else{
            $uri = substr($real_uri, 1, strpos($real_uri, '?') - 1);
        }
        echo ($uri == $url)? 'class="active"' : '';
    }



    /**
     * удаление всех emails
     */
    function del_all(){
        Store::delete_all_emails();
    }

    /**
     * пометка всех emails как неотправленных
     */
    function mark_as_nosend(){
        Store::mark_allemail_as_nosended();
    }

    /**
     * пометка всех emails как отправленных
     */
    function mark_as_send(){
        Store::mark_allemail_as_sended();
    }

    /**
     * удаление всех emails
     */
    function del_sended(){
        Store::delete_all_sended_emails();
    }


    /**
     * пересоздание таблиц базы данных
     */
    function reset_db(){
        Store::reset_database();
    }

    /**
     * отправка письма
     */
    function send_email(){
        $email = (isset($_POST['email']))? $_POST['email'] : '';
        $email_data = Store::get_email($email);
        if (is_array($email_data)){
            $to = $email_data[0]['email'];
            $subject = $email_data[0]['subject'];
            $message = $email_data[0]['message'];
            $from = Store::get_option('from');
            $images = $email_data[0]['images'];
            $images = explode(',', $images);
            $files = array();
            $count = 0;
            if(is_array($images)){
                foreach($images as $image){
                    if(!file_exists($image)) continue;
                    $count++;
                    $files[$count]['name'] = $image;
                    $files[$count]['type'] = 'application/octet-stream';
                }
            }

            $res = Mailer::send($to, $subject, $message, $from, $files);
            Store::change_email_status($to, $res['status'], $res['message']);
        }
    }

    /**
     * возвращает список изображений, имеющихся в каталоге изображений
     * @param $dir
     */
    function get_images($dir){
        $images = array();
        if (is_array($files = scandir($dir))){
            foreach($files as $file){
                if ($file == '.' || $file == '..') continue;
                $images[$file] = IMG_DIR . $file;
            }
        }
        return $images;
    }


?>