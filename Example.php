<?php
include "HofUniversityCanteenData/Helper.php";
use function HofUniversityCanteenData\convertAllUnconvertedDataToWeek;
use function HofUniversityCanteenData\convertWeekToFormatedGermanString;
use function HofUniversityCanteenData\downloadAllUnconvertedData;
use function HofUniversityCanteenData\filterWeekByAttributeIdsAndDayNumber;



$data=downloadAllUnconvertedData(18, 8);

$week=convertAllUnconvertedDataToWeek($data);
error_log(convertWeekToFormatedGermanString($week));

$filteredWeek=filterWeekByAttributeIdsAndDayNumber($week, array(1), 20);
error_log(convertWeekToFormatedGermanString($filteredWeek));