<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Mail {

////////////////////////////////////////////////////////////////////////////////

    static function send($to, $params, $tags = array()){
        $params = array_merge(array(
            'type'   => b::config('mail.type'),
            'attach' => array()
        ), (is_array($params) ? $params : self::bender($params)));

        $tags = arr::merge(array('config' => b::config(), 'q' => b::q()), $tags);
        $params['subject'] = str::format($params['subject'], $tags);

        $mail = new Zend_Mail(b::config('mail.charset'));
        $mail->setSubject($params['subject']);

        if (preg_match('/(.*)(?>\s<([^>]*)>)/', $to, $match))
            $mail->addTo($match[2], $match[1]);
        else
            $mail->addTo($to);

        if (preg_match('/(.*)(?>\s<([^>]*)>)/', b::config('mail.sender'), $match))
            $mail->setFrom($match[2], $match[1]);
        else
            $mail->setFrom(b::config('mail.sender'));

        if (is_array($params['message'])){
            if ($params['message']['text'])
                $mail->setBodyText(str::format($params['message']['text'], $tags));

            if ($params['message']['html'])
                $mail->setBodyHtml(str::format($params['message']['html'], $tags));
        } elseif ($params['type'] == 'text/html'){
            $mail->setBodyHtml(str::format($params['message'], $tags));
        } else {
            $mail->setBodyText(str::format($params['message'], $tags));
        }

        foreach ((array)$params['attach'] as $filename){
            $attach = $mail->createAttachment(file_get_contents($filename));
            $attach->filename = basename($filename);
        }

        try {
            if (b::config('mail.smtp')){
                $config = b::config('mail.smtp');
                $config['auth'] = 'login';

                $transport = new Zend_Mail_Transport_Smtp(b::config('mail.smtp.host'), $config);
            }

            $mail->send($transport);

            return true;
        } catch (Exception $e){
        }
    }

////////////////////////////////////////////////////////////////////////////////

    static function bender($message){
        if (is_file($file = file::path($message.'.eml')))
            $message = file_get_contents($file);

        preg_match('/subject:(.*)/i', $message, $subject);
        preg_match('/attach:(.*)/i', $message, $attach);
        preg_match('/type:(.*)/i', $message, $type);

        $message = ltrim(str_replace(array($subject[0], $attach[0], $type[0]), '', $message));
        $message = trim($message)."\n";
        $subject = trim($subject[1]);
        $attach  = array_map('trim', explode(',', $attach[1]));

        if (!$type = trim($type[1]))
            $type = b::config('mail.type');

        return compact('subject', 'message', 'attach', 'type');
    }

////////////////////////////////////////////////////////////////////////////////

}
