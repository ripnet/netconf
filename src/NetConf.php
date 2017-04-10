<?php

namespace ripnet\NetConf;

use phpseclib\Net\SSH2;

class NetConf {
    /**
     * @var SSH2
     */
    protected $ssh;

    /**
     * @var int
     */
    protected $sessionID;

    /**
     * @var int
     */
    protected $messageID;

    public function __construct($hostname, $username, $password, $port = 830) {
        $this->ssh = new SSH2($hostname, $port);
        $this->ssh->setWindowSize(-1, -1);
        if (!$this->ssh->login($username, $password)) {
            throw new \Exception("Authentication failed.");
        }
        $this->ssh->startSubsystem("netconf");
        $this->read("</hello>");
    }

    public function read($end) {
        return str_replace("{$end}\n]]>]]>","{$end}", $this->ssh->read("{$end}\n]]>]]>"));
    }

    public function sendHello() {
        $h = new \SimpleXMLElement("<hello><capabilities><capability>urn:ietf:params:xml:ns:netconf:base:1.0</capability><capability>urn:ietf:params:ns:netconf:capability:startup:1.0</capability></capabilities></hello>");
        $this->send($h, null);
    }

    public function send($data, $eom = "</rpc-reply>") {
        $this->ssh->write($data . "]]>]]>\n");
        return $this->read($eom);
    }

}