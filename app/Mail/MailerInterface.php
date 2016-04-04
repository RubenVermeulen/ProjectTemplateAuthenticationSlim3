<?php
/**
 * Created by PhpStorm.
 * User: ruben
 * Date: 4/04/2016
 * Time: 20:19
 */

namespace App\Mail;


use Slim\Http\Response;

interface MailerInterface
{
    /**
     * Send an email to the recipient.
     *
     * @param Response $response
     * @param $template
     * @param array $data
     * @param array $credentials
     * @return mixed
     */
    public function sendMessage($template, array $data, array $credentials);
}