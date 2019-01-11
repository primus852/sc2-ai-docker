<?php
/**
 * Created by PhpStorm.
 * User: torsten
 * Date: 09.01.2019
 * Time: 16:29
 */

namespace App\Util\TelegramBot;


use App\Entity\BotApiChat;
use App\Util\Dashboard\Dashboard;
use Doctrine\Common\Persistence\ObjectManager;

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
        $this->url = 'https://api.telegram.org/bot' . $this->token;

    }

    /**
     * @param string $msg
     * @param int|null $user
     */
    public function send_to_user(string $msg, ?int $user = null)
    {

        $mod = '/sendMessage?';

        $userId = $user === null ? $this->user_id : $user;

        $params = array(
            'chat_id' => $userId,
            'text' => $msg,
        );


        $this->send_request($this->url . $mod . http_build_query($params));

    }

    /**
     * @param ObjectManager $em
     * @throws TelegramBotException
     */
    public function update_bot(ObjectManager $em)
    {
        $mod = '/getUpdates';

        $result = $this->send_request($this->url . $mod);

        /**
         * Check if we have a valid response
         */
        if (!$result['info']['http_code'] === 200) {
            throw new TelegramBotException('Error Reading Bot Api');
        }

        /**
         * Convert to Array
         */
        $json = json_decode($result['result'], true);

        /**
         * See if we have a valid Telegram Response
         */
        if ($json['ok'] !== true) {
            throw new TelegramBotException('Invalid Telegram Response: ' . $json['description']);
        }

        /**
         * Get all messages
         */
        foreach ($json['result'] as $message) {

            /**
             * Get the Update ID and see if we already have it in our DB
             */
            $chat = $em->getRepository(BotApiChat::class)->findOneBy(array(
                'updateId' => $message['update_id']
            ));

            if ($chat !== null) {
                continue;
            }

            try {
                $chat = new BotApiChat();
                $chat->setUpdateId($message['update_id']);
                $chat->setFromUserId($message['message']['from']['id']);
                $chat->setSentStamp(\DateTime::createFromFormat('U', $message['message']['date']));
                $chat->setSentText(substr($message['message']['text'], 0, 254));
                $chat->setIsAnswered(false);
                $chat->setAnswerSent('none');
                $em->persist($chat);

                $em->flush();
            } catch (\Exception $e) {
                throw new TelegramBotException('MySQL Error: ' . $e->getMessage());
            }
        }
    }

    /**
     * @param ObjectManager $em
     * @throws TelegramBotException
     */
    public function send_answers(ObjectManager $em)
    {

        /**
         * Get all not answered
         */
        $unanswered = $em->getRepository(BotApiChat::class)->findBy(array(
            'isAnswered' => false
        ));

        foreach ($unanswered as $chat) {

            $answer = $this->choose_answer($chat->getSentText(), $em);

            $this->send_to_user($answer, $chat->getFromUserId());

            $chat->setIsAnswered(true);
            $chat->setAnswerSent($answer);
            $em->persist($chat);

            try {
                $em->flush();
            } catch (\Exception $e) {
                throw new TelegramBotException('Error saving Answer to DB: ' . $e->getMessage());
            }

        }


    }

    /**
     * @param string $sent
     * @param ObjectManager $em
     * @return string
     * @throws TelegramBotException
     */
    private function choose_answer(string $sent, ObjectManager $em)
    {

        $dashboard = new Dashboard($em);
        $result = null;

        /**
         * Send on-/offline
         */
        if (strtolower($sent) === '/status' || strtolower($sent) === 'status') {

            try {
                $result = $dashboard->last_seen();
            } catch (\Exception $e) {
                throw new TelegramBotException('Error choosing Answer: ' . $e->getMessage());
            }

            $last_seen = $result['last'] !== null ? $result['last']->format('d.m.Y H:i') : 'never';

            return 'Status: ' . $result['status'] . chr(10) . 'Last Seen: ' . $last_seen;

        }

        if (strtolower($sent) === '/win' || strtolower($sent) === 'win') {

            try {
                $result = $dashboard->get_summary();
            } catch (\Exception $e) {
                throw new TelegramBotException('Error choosing Answer: ' . $e->getMessage());
            }

            return
                'Stats for ' . $dashboard->total_episodes() . ' Episodes' . chr(10) .
                'Wins: ' . $result['win'] . '%' . chr(10) .
                'Losses: ' . $result['loss'] . '%' . chr(10) .
                'Draws: ' . $result['draw'] . '%' . chr(10);

        }

        if (strtolower($sent) === '/win100' || strtolower($sent) === 'win100') {

            try {
                $result = $dashboard->get_summary(100);
            } catch (\Exception $e) {
                throw new TelegramBotException('Error choosing Answer: ' . $e->getMessage());
            }

            return
                'Stats for the last 100 Episodes' . chr(10) .
                'Wins: ' . $result['win'] . '%' . chr(10) .
                'Losses: ' . $result['loss'] . '%' . chr(10) .
                'Draws: ' . $result['draw'] . '%' . chr(10);

        }

        if (strtolower($sent) === '/help' || strtolower($sent) === 'help') {

            return
                'Command Helper' . chr(10) . chr(10).
                'Currently available:' . chr(10) .
                '/status - Sends the Status of the agent running' . chr(10) .
                '/win - Sends the current Winrate' . chr(10) .
                '/win100 - Sends the Winrate for the last 100 Games' . chr(10) .
                '/help - this' . chr(10);

        }


        /**
         * Unknown command
         */
        return
            'Unknown command "' . $sent . '" ' . chr(10) . chr(10).
            'Currently available:' . chr(10) .
            '/status - Sends the Status of the agent running' . chr(10) .
            '/win - Sends the current Winrate' . chr(10) .
            '/win100 - Sends the Winrate for the last 100 Games' . chr(10) .
            '/help - Shows all available commands' . chr(10);

    }

    /**
     * @param $url
     * @return array
     */
    private function send_request($url)
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'SC2AI Dashboard',
        ));

        $result = curl_exec($curl);
        $info = curl_getinfo($curl);

        curl_close($curl);

        return array(
            'result' => $result,
            'info' => $info
        );

    }


}