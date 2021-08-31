<?php
include "HofUniversityCanteenAPI/Helper.php";
use function HofUniversityCanteenAPI\convertAllUnconvertedDataToWeek;
use function HofUniversityCanteenAPI\convertWeekToFormatedGermanString;
use function HofUniversityCanteenAPI\downloadAllUnconvertedData;
use function HofUniversityCanteenAPI\filterWeekByAttributeIdsAndDayNumber;



$data=downloadAllUnconvertedData(18, 8);

$week=convertAllUnconvertedDataToWeek($data);
error_log(convertWeekToFormatedGermanString($week));

$filteredWeek=filterWeekByAttributeIdsAndDayNumber($week, array(1), 20);
error_log(convertWeekToFormatedGermanString($filteredWeek));