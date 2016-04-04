<?php


namespace App\Mail;


use Mailgun\Mailgun;
use Slim\Http\Response;
use Slim\Views\Twig;

class MailerMailgun implements MailerInterface
{
    /**
     * Instance of the used mailer.
     *
     * @var
     */
    private $mailer;

    /**
     * Contains the config set in the config file.
     *
     * @var
     */
    private $config;

    /**
     * Makes views accessible.
     *
     * @var
     */
    private $view;

    /**
     * MailerMailgun constructor.
     *
     * @param $mailer
     * @param $config
     * @param $view
     */
    public function __construct(Mailgun $mailer, $config, Twig $view) {
        $this->mailer = $mailer;
        $this->config = $config;
        $this->view = $view;
    }


    /**
     * Send an email to the recipient.
     *
     * @param $template
     * @param array $data
     * @param array $credentials
     * @return mixed
     * @throws \Mailgun\Messages\Exceptions\MissingRequiredMIMEParameters
     */
    public function sendMessage($template, array $data, array $credentials) {
        $this->mailer->sendMessage(
            $this->config->get('mailgun.domain'),
            [
                'from' => $credentials['from'],
                'to' => $credentials['to'],
                'subject' => $credentials['subject'],
                'html' => $this->view->fetch($template, $data),
            ]
        );
    }
}