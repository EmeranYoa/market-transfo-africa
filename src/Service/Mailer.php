<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class Mailer
{
    public function __construct(
        private readonly Environment $twig,
        private readonly MailerInterface $mailer
    )
    {
    }

    public function createEmail(string $template, array $data = []): Email
    {
//        $this->twig->addGlobal('format', 'html');
//        $html = $this->twig->render($template, array_merge($data, ['layout' => 'mails/base.html.twig']));
//        $this->twig->addGlobal('format', 'text');
//        $text = $this->twig->render($template, array_merge($data, ['layout' => 'mails/base.text.twig']));

        return (new TemplatedEmail())
            ->from('noreply@transfoafricainc.com')
            ->htmlTemplate('mails/'. $template .'.html.twig')
//            ->textTemplate('mails/'. $template .'.txt.twig')
            ->context($data);
    }

    public function send(Email $email):void
    {
        $this->mailer->send($email);
    }
}