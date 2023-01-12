<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property array $calendarData List of weeks data. Structure:

 */
class Calendar extends Model
{

  /**
   * @param Task[] $taskList
   * @return array
   */
  static public function getWeekListData($taskList)
  {
    // Preparing som variable initialization for the main loop.
    $weekListData = [];

    // We use de list of tasks to deduce how much in the future we need to go.
    $remainingDaysTotal = 0.0;
    foreach ($taskList as $id => $task) {
      $remainingDaysTotal += $task->remainingDaysEstimate;
    }
    $remainingDaysTotal;

    // We need to start from the first day of the week.
    // In this case we consider that day to be Monday.
    // So step back until Monday is found. 
    $initDate = now();
    while (!$initDate->isDayOfWeek(Carbon::MONDAY)) {
      $initDate->subDays(1);
    }
    $currentDayFound = false;

    // We loop through all the days that are going to been shown in the calendar.
    for (
      // Loop structure initialization
      $date = $initDate->copy(),
      $remainingDaysCount = $remainingDaysTotal,
      $weekData = [];
      // Loop structure "while" condition
    !($remainingDaysCount <= 0 && $date->isDayOfWeek(Carbon::MONDAY));
      // Next loop initialization
      $date->addDays(1),
      $firstIteration = false
    ) {
      // We create and populate the basic day data.
      $dayData = [
        "dayNumber"     => $date->day,
        "monthName"     => $date->monthName,
        "isPlaceholder" => false,
        "isCurrentDay"  => $date->isCurrentDay(),
        "showMonth"     => false,
        "remainingDaysCount" => $remainingDaysCount,
      ];

      // Check whether we have already reach the current day.
      if ($dayData["isCurrentDay"]) {
        $currentDayFound = true;
      }

      // Days after all tasks are finished are consider placeholders too
      if($remainingDaysCount <= 0){
        $dayData["isPlaceholder"] = true;
      }

      // If have reach the current day we can star subtracting de remaining days
      // Otherwise we mark the day as a placeholder. 
      if ($currentDayFound) {
        $remainingDaysCount -= 1;
      } else {
        $dayData["isPlaceholder"] = true;
      }

      // We set to show the month's name if:
      // - it is the first day of the month
      // - it is the current day (i.e, first productive day)
      if ($dayData["isCurrentDay"] || $dayData["dayNumber"] == 1) {
        $dayData["showMonth"] = true;
      }

      // After the $dayData es complete, we add it to the $weekData list
      $weekData[$date->dayOfWeekIso] = $dayData;

      // If it's the last day of the week, we add the completed week to the week list,
      // and initialize the next one.
      if ($date->isDayOfWeek(Carbon::SUNDAY) /*|| $remainingDaysCount <= 0*/) {
        $weekListData[] = $weekData;
        $weekData = [];
      }
    }

    // Return the already populated data.
    return $weekListData;
  }

  /**
   * @param array   $calendarData Pass by reference calendar data.
   * @param Task[]  $taskList
   */
  static public function populateWithTasks(&$calendarData, $taskList)
  {
    // TODO
  }

  /**
   * @return array List of weeks data. Basic structure:
   *   [
   *    "week-01" => [
   *      "days": [
   *        [
   *          "dayNumber": <int: Number in the month>,
   *          ...
   *        ],
   *        ...
   *      ],
   *      "tasks" : [
   *        [
   *          "text": <string>,
   *          "weekPortion": <float: 0.0-100.0>,
   *          ...
   *        ],
   *        ...
   *      ],
   *    ],
   *    ...
   *  ]
   */
  static public function getCalendarData()
  {
    $taskList = Task::getTaskList(true);
    $calendarData = self::getWeekListData($taskList);
    self::populateWithTasks($calendarData, $taskList);
    return $calendarData;
  }
}
