<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Mail {

////////////////////////////////////////////////////////////////////////////////

    static function send($to, $params, $tags = array()){        $params = array_merge(array(
            'type'   => b::config('mail.type'),
            'attach' => array()
        ), (is_array($params) ? $params : self::bender($params)));

        $tags = array_merge(array('config' => b::config(), 'q' => b::q()), $tags);

        $params['subject'] = str::format($params['subject'], $tags);

        $mail = new Nomad_MimeMail;
        $mail->debug_status = 'no';
        $mail->set_subject($params['subject']);
        $mail->set_charset('utf-8');

        if (preg_match('/(.*)(?>\s<([^>]*)>)/', $to, $match))
            $mail->set_to($match[2], $match[1]);
        else
            $mail->set_to($to);

        if (preg_match('/(.*)(?>\s<([^>]*)>)/', b::config('lib.mail.sender'), $match))
            $mail->set_from($match[2], $match[1]);
        else
            $mail->set_from(b::config('lib.mail.sender'));

        if (is_array($params['message'])){            if ($params['message']['text'])
                $mail->set_text(str::format($params['message']['text'], $tags));
            if ($params['message']['html'])
                $mail->set_html(str::format($params['message']['html'], $tags));
        } elseif ($params['type'] == 'text/html'){
            $mail->set_html(str::format($params['message'], $tags));
        } else {
            $mail->set_text(str::format($params['message'], $tags));
        }

        if (b::config('lib.mail.smtp.on')){
            $mail->set_smtp_host(b::config('lib.mail.smtp.host'), b::config('lib.mail.smtp.port'));
            $mail->set_smtp_auth(b::config('lib.mail.smtp.user'), b::config('lib.mail.smtp.password'));
        }

        foreach ($params['attach'] as $filename)
            $mail->add_attachment($filename, basename($filename));

        return $mail->send();
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
        $attach  = arr::trim(explode(';', $attach[1]));

        if (!$type = trim($type[1]))
            $type = b::config('lib.mail.type');

        return compact('subject', 'message', 'attach', 'type');
    }

////////////////////////////////////////////////////////////////////////////////

}