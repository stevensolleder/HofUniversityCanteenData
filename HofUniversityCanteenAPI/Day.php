<?php
namespace HofUniversityCanteenAPI;



class Day
{
    public $name;
    public $date;
    public $mainCourses;
    public $sideDishes;
    public $desserts;
    public $salads;



    public function __construct()
    {
        $this->mainCourses=array();
        $this->sideDishes=array();
        $this->desserts=array();
        $this->salads=array();
    }
}