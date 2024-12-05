<?php
namespace HofUniversityCanteenData;



include "Week.php";
include "Day.php";
include "Dish.php";



function getAttributeIdsTable():array
{
    return array
    (
        5 => 'Schwein',
        3 => 'Geflügel',
        7 => 'Vegetarisch',
        1 => 'Hausgemacht',
        8 => 'Rind',
        6 => 'Fisch',
        14 => 'Tierisches Lab/Gelatine/Honig',
        4 => 'Regional',
        11 => 'Schaf',
        12 => 'Meeresfrüchte',
        10 => 'Vegan',
        15 => 'Kräuterküche',
        2 => 'Wild',
        9 => 'Nachhaltiger Fang',
        13 => 'Mensa-Vital, eine Marke der Studentenwerke'
    );
}


function downloadAllUnconvertedData(int $dayNumber, int $monthNumber):\SimpleXMLElement
{
    return new \SimpleXMLElement(str_replace('mensa:', '', file_get_contents('https://www.studentenwerk-oberfranken.de/?eID=bwrkSpeiseplanRss&tx_bwrkspeiseplan_pi2%5Bbar%5D=340&tx_bwrkspeiseplan_pi2%5Bdate%5D=' . $dayNumber . '-' . $monthNumber . '-' . date('Y'))));
}


function convertAllUnconvertedDataToWeek(\SimpleXMLElement $allUnconvertedData):Week
{
    $unconvertedDays = $allUnconvertedData->channel->item->mensa->speiseplan->tag;
    $week = new Week();

    foreach ($unconvertedDays as $unconvertedDay) {
        $day = new Day();
        $day->name = (string)$unconvertedDay['wochentag'];
        $day->date = new \DateTime((string)$unconvertedDay['datum']);

        if (!isset($unconvertedDay->bereich->kategorie)) {
            continue;
        }

        $categories = $unconvertedDay->bereich->kategorie;
        $addDishesToCategory=function (object &$dishes, array &$category):void
        {
            if ($dishes->count() > 1) {
                foreach ($dishes as $dish) {
                    $category[] = new Dish((string)$dish['name'], (array)$dish->gerichtAttribute->gerichtAttribut, (double)$dish->gerichtPreise->preis[0]);
                }
            } else {
                if ($dishes->count() != 0) {
                    $category[] = new Dish((string)$dishes['name'], (array)$dishes->gerichtAttribute->gerichtAttribut, (double)$dishes->gerichtPreise->preis[0]);
                }
            }
        };
        $addDishesToCategory($categories[0]->gericht, $day->mainCourses);
        $addDishesToCategory($categories[1]->gericht, $day->sideDishes);
        $addDishesToCategory($categories[2]->gericht, $day->desserts);
        $addDishesToCategory($categories[3]->gericht, $day->salads);

        $week->days[] = $day;
    }

    return $week;
}


function filterWeekByAttributeIdsAndDayNumber(Week $week, array $attributeIds, int $dayNumber):Week
{
    if (count($attributeIds) != 0) {
        $attributesFilter = function ($dish) use ($attributeIds) {
            return count(array_intersect($dish->attributes, $attributeIds)) > 0;
        };
        foreach ($week->days as $day) {
            $day->mainCourses = array_filter($day->mainCourses, $attributesFilter);
            $day->sideDishes = array_filter($day->sideDishes, $attributesFilter);
            $day->desserts = array_filter($day->desserts, $attributesFilter);
            $day->salads = array_filter($day->salads, $attributesFilter);
        }
    }

    if ($dayNumber != -1) {
        $week->days = array_filter($week->days, function ($day) use ($dayNumber) {
            return ((int)$day->date->format('d')) == $dayNumber;
        });
    }

    return $week;
}


function convertWeekToFormatedGermanString(Week $week):string
{
    foreach($week->days as $index => $day)
    {
        if(empty($day->mainCourses)&&empty($day->sideDishes)&&empty($day->desserts)&&empty($day->salads))
        {
            continue;
        }

        $output="";

        if(!$index==array_key_first($week->days))
        {
            $output .= "\n";
        }

        $output.=('📆 '.$day->name.' ('.$day->date->format('j.n.Y')."):\n");

        $convertCategoryToString=function (array $category):string
        {
            $output='';

            foreach($category as $dish)
            {
                $attributeList='';

                foreach($dish->attributes as $id => $name)
                {
                    $attributeList.=getAttributeIdsTable()[$name];

                    if(!$id==array_key_last($dish->attributes))
                    {
                        $attributeList.=', ';
                    }
                }

                $output.=('📌 '.$dish->name.', '.number_format($dish->collegeStudentPrice, 2, ',', '').'€ ('.$attributeList.")\n");
            }

            return str_replace('\"', '', $output);
        };

        if(!empty($day->mainCourses))
        {
            $output.="🍝 Hauptgerichte:\n";
            $output.=$convertCategoryToString($day->mainCourses);
            $output.="\n";
        }

        if(!empty($day->sideDishes))
        {
            $output.="🍟 Beilagen:\n";
            $output.=$convertCategoryToString($day->sideDishes);
            $output.="\n";
        }

        if(!empty($day->desserts))
        {
            $output .= "🍰 Nachspeisen:\n";
            $output .= $convertCategoryToString($day->desserts);
            $output .= "\n";
        }

        if(!empty($day->salads))
        {
            $output .= "🥗 Salate:\n";
            $output .= $convertCategoryToString($day->salads);
            $output .= "\n";
        }
    }

    return $output;
}