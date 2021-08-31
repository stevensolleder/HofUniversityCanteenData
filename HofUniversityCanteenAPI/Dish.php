<?php
namespace HofUniversityCanteenAPI;



class Dish
{
    public $name;
    public $attributes;
    public $collegeStudentPrice;



    public function __construct($name, $attributes, $collegeStudentPrice)
    {
        $this->name=$name;
        $this->attributes=$attributes;
        $this->collegeStudentPrice=$collegeStudentPrice;
    }
}
