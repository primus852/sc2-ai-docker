<?php
/**
 * Created by PhpStorm.
 * User: torsten
 * Date: 09.01.2019
 * Time: 16:29
 */

namespace App\Util\TelegramBot;


class TelegramBot
{

    private $token;
    private $user_id;
    private $url;

    /**
     * TelegramBot constructor.
     */
    public function __construct()
    {

        $this->token = getenv('TELEGRAM_TOKEN');
        $this->user_id = getenv('TELEGRAM_USER');
        $this->url = 'https://api.telegram.org/bot' . $this->token . '/sendMessage?';

    }

    public function send_update(string $msg)
    {

        $params = array(
            'chat_id' => $this->user_id,
            'text' => $msg,
        );


        $code = $this->send_request($this->url . http_build_query($params));

        if($code === 200){

        }

    }

    private function send_request($url)
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'SC2AI Dashboard',
        ));

        curl_exec($curl);
        $info = curl_getinfo($curl);

        curl_close($curl);

        return $info['http_code'];

    }


}