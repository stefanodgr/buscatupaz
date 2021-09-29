<?php

namespace App\Http\Helper;
use Illuminate\Support\Facades\Response as Response;

/**
 * Created by PhpStorm.
 * User: Personal
 * Date: 26/10/2017
 * Time: 8:08 AM
 */


class Calendar
{
    public $events;
    public $title;
    public $author;


    public function __construct($parameters) {
        $parameters += array(
            'events' => array(),
            'title' => 'Calendar',
            'author' => 'Baselang'
        );
        $this->events = $parameters['events'];
        $this->title  = $parameters['title'];
        $this->author = $parameters['author'];
    }

    public function show() {

        $response = Response::make($this->generateString(), 200);

        $response->header('Content-Type', 'text/calendar');
        $response->header('Content-Disposition', 'attachment; filename="calendar.ics"');
        $response->header('Content-Length', strlen($this->generateString()));
        $response->header('Connection', 'close');

        return $response;
    }


    public function generateString() {
        $content = "BEGIN:VCALENDAR\r\n"
            . "VERSION:2.0\r\n"
            . "PRODID:-//" . $this->author . "//NONSGML//EN\r\n"
            . "X-WR-CALNAME:" . $this->title . "\r\n"
            . "CALSCALE:GREGORIAN\r\n";
        foreach($this->events as $event) {
            $content .= $event->generateString();
        }
        $content .= "END:VCALENDAR";
        return $content;
    }

}