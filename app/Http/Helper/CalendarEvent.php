<?php

namespace App\Http\Helper;
use Illuminate\Support\Facades\Response as Response;

/**
 * Created by PhpStorm.
 * User: Personal
 * Date: 26/10/2017
 * Time: 8:08 AM
 */
class CalendarEvent {
    private $uid;
    private $start;
    private $end;
    private $summary;
    private $description;

    public function __construct($parameters) {
        $parameters += array(
            'summary' => 'Untitled Event',
            'description' => '',
            'location' => ''
        );
        if (isset($parameters['uid'])) {
            $this->uid = $parameters['uid'];
        } else {
            $this->uid = uniqid(rand(0, getmypid()));
        }
        $this->start = $parameters['start'];
        $this->end = $parameters['end'];
        $this->summary = $parameters['summary'];
        $this->description = $parameters['description'];
        $this->location = $parameters['location'];
        return $this;
    }

    private function formatDate($date) {
        return $date->format("Ymd\THis\Z");
    }

    private function formatValue($str) {
        return addcslashes($str, ",\\;");
    }

    public function generateString() {
        $created = new \DateTime();
        $content = '';
        $content = "BEGIN:VEVENT\r\n"
            . "UID:{$this->uid}\r\n"
            . "DTSTART:{$this->formatDate($this->start)}\r\n"
            . "DTEND:{$this->formatDate($this->end)}\r\n"
            . "DTSTAMP:{$this->formatDate($this->start)}\r\n"
            . "CREATED:{$this->formatDate($created)}\r\n"
            . "DESCRIPTION:{$this->formatValue($this->description)}\r\n"
            . "LAST-MODIFIED:{$this->formatDate($this->start)}\r\n"
            . "LOCATION:{$this->location}\r\n"
            . "SUMMARY:{$this->formatValue($this->summary)}\r\n"
            . "SEQUENCE:0\r\n"
            . "STATUS:CONFIRMED\r\n"
            . "TRANSP:OPAQUE\r\n"
            . "END:VEVENT\r\n";
        return $content;
    }

}

