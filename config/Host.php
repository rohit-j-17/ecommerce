<?php

class Host
{
    private $path;
    private $url;
    //private $env;
    public function __construct()
    {
        $env = "DEBUG";
        //$env= "PROD";

        if ($env == "PROD") { //hosting
            $this->path = $_SERVER['DOCUMENT_ROOT'] . '/';
            $this->url = (isset($_SERVER['HTTPS']) ? "https" : "http") . '://' . $_SERVER['HTTP_HOST'] . '/';
        } else { //local
            $this->path = $_SERVER['DOCUMENT_ROOT'] . '/' . 'ecom/';
            $this->url = (isset($_SERVER['HTTPS']) ? "https" : "http") . '://' . $_SERVER['HTTP_HOST'] . '/' . 'ecom/';
        }
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getUrl()
    {
        return $this->url;
    }
}
