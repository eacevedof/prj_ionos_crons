<?php
abstract class AbstractCron
{
    protected $projects;

    public function __construct()
    {
        $this->projects = include_once("projects.php");
    }
}