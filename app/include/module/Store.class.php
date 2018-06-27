<?php
/**
 * Created by PhpStorm.
 * User: user1
 * Date: 22.05.16
 * Time: 15:01
 */
    class Store{

        static protected $db = null;

        static public function init(){
            self::$db = MySQL::get_instance();
        }

        /**
         * пересоздание таблиц БД
         */
        static public function reset_database(){
            self::init();
            self::$db->delete_tables();
            self::$db->create_tables();
            self::$db->insert('settings', array('mail_per_once' => 50));
            self::$db->insert('settings', array('pause' => 3));
            self::$db->insert('settings', array('enable' => 0));
            self::$db->insert('settings', array('from' => 'admin@mail.ru'));
        }

        static public function insert_emails_from_file($filename){
            self::init();
            $handle = @fopen($filename, "r");
            if ($handle) {
                $data = array();
                while (($email = fgets($handle, 4096)) !== false) {
                    $email = trim($email);
                    $ctime = time();
                    if (!$email) continue;
                    $data[] = array('email' => $email, 'ctime' => $ctime);
                }
                self::$db->insert('emails', $data);
            }
        }


        static public function add_email($email, $subject='none', $message='none'){
            self::init();
            $ctime = time();
            return self::$db->insert('emails', array('email' => $email, 'subject' => $subject, 'message' => $message, 'ctime' => $ctime));
        }

        /*получение email по значению, id или всех*/
        static public function get_email($id=null){
            self::init();
            if ($id == null){
                return self::$db->get('emails');
            }elseif(is_string($id)){
                return self::$db->get('emails', array('email=' => $id), 'LIMIT 1');
            }elseif(is_int($id)){
                 return self::$db->get('emails', array('id=' => $id), 'LIMIT 1');
            }else{
                return null;
            }
        }

        /*получение отправленных emails*/
        static public function get_all_sended_emails(){
            self::init();
            return self::$db->get('emails', array('status=' => 1));
        }

        /*получение неотправленных emails*/
        static public function get_all_nosended_emails(){
            self::init();
            return self::$db->get('emails', array('status=' => 0));
        }

        /*получение параметров неотправленных emails*/
        static public function get_params_all_nosended_emails(){
            self::init();
            $rows = self::$db->get('emails', array('status=' => 0), 'LIMIT 1');
            if (is_array($rows) && count($rows)){
                return $rows[0];
            }
            return false;
        }

        /*пометка email как отработанного*/
        static function mark_email_as_sended($email){
            self::init();
            $ctime = time();
            return self::$db->update('emails', array('status' => 1, 'ctime' => $ctime), array('email=' => $email));
        }

        /*изменение статуса письма в зависимости от результатов отправки*/
        static public function change_email_status($email, $result, $message){
            self::init();
            $stime = time();
            if ($result){
                return self::$db->update('emails', array('status' => 1, 'stime' => $stime, 'report' => $message), array('email=' => $email));
            }else{
                return self::$db->update('emails', array('status' => 0, 'stime' => $stime, 'report' => $message), array('email=' => $email));
            }
        }

        /*получение статуса отправки*/
        static public function get_email_report($email){
            self::init();
            $rows = self::$db->get('emails', array('email=' => $email), 'LIMIT 1');
            if (is_array($rows) && count($rows)){
                return $rows[0]['report'];
            }
            return null;
        }

        /*пометка всех email как неотработанных*/
        static function mark_allemail_as_nosended(){
            self::init();
            $ctime = time();
            return self::$db->update('emails', array('status' => 0, 'ctime' => $ctime, 'report' => ''));
        }

        /*пометка всех email как отработанных*/
        static function mark_allemail_as_sended(){
            self::init();
            $ctime = time();
            return self::$db->update('emails', array('status' => 1, 'ctime' => $ctime));
        }

        /**получение неотработанных emails в кoличестве
         * @param $n
         * @return array|bool|mysqli_result|null
         */
        static public function get_nosended_emails($n){
            self::init();
            return self::$db->get('emails', array('status=' => 0), "LIMIT $n");
        }

        /**
         * установка темы и тела писем
         * @param $subject
         * @param $message
         * @param $images
         * @return array|bool|mysqli_result|null
         */
        static public function set_nosended_email_param($subject, $message, $images){
            self::init();
            $images = implode(',',$images);
            $ctime = time();
            return self::$db->update('emails', array('subject' => $subject, 'message' => $message, 'images' => $images, 'ctime' => $ctime), array('status=' => 0));
        }

        /**
         * установка темы и тела всех писем
         * @param $subject
         * @param $message
         * @param $images
         * @return array|bool|mysqli_result|null
         */
        static public function set_email_param($subject, $message, $images){
            self::init();
            $images = implode(',',$images);
            $ctime = time();
            return self::$db->update('emails', array('subject' => $subject, 'message' => $message, 'images' => $images, 'ctime' => $ctime));
        }

        /**
         * удаление всех emails
         * @return array|bool|mysqli_result
         */
        static public function delete_all_emails(){
            self::init();
            return self::$db->delete('emails');
        }

        /**
         * удаление всех отправленных emails
         * @return array|bool|mysqli_result
         */
        static public function delete_all_sended_emails(){
            self::init();
            return self::$db->delete('emails', array('status=' => 1));
        }

        /**
         * получение значения настройки
         * @param $key
         * @return null
         */
        static public function get_option($key){
            self::init();
            $rows = self::$db->query("SELECT count(*) AS cnt FROM settings WHERE `key`='{$key}'");
            if (is_array($rows) && count($rows) && $rows[0]['cnt'] > 0){
                $rows = self::$db->get('settings', array('`key`=' => $key), 'LIMIT 1');
                return $rows[0]['value'];
            }
            return null;
        }

        /**
         * установка значения настройки
         * @param $key
         * @param $value
         * @return array|bool|mysqli_result|null
         */
        static public function set_option($key, $value){
            if (self::get_option($key) == null){
                return self::$db->insert('settings', array('key'=> $key, 'value' => $value));
            }else{
                return self::$db->update('settings', array('value' => $value), array('`key`=' => $key));
            }
        }
    }




