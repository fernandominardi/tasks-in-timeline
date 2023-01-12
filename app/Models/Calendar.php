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

  function __construct()
  {
    /*$this->weekList;
    $this->currentWeekDay;
    $this->totalNumberOfDaysRemaining;
    $this->taskList;*/

    //$this->calendarData = [];

    //$this->taskList = Task::getTaskList(true);
  }

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
      $remainingDaysCount > 0;
      // Next loop initialization
      $date->addDays(1),
      $firstIteration = false
    ) {
      $dayData = [
        "number"            => $date->day,
        "month-label"       => $date->monthName,
        "is-placeholder"    => false,
        "is-current-day"    => $date->isCurrentDay(),
        "show-month"        => false,
      ];

      if ($dayData["is-current-day"]) {
        $currentDayFound = true;
      }

      if ($currentDayFound) {
        $remainingDaysCount -= 1;
      } else {
        $dayData["is-placeholder"] = true;
      }

      if ($dayData["is-current-day"] || $dayData["number"] == 1) {
        $dayData["show-month"] = true;
      }

      $weekData[$date->dayOfWeekIso] = $dayData;

      if ($date->isDayOfWeek(Carbon::SUNDAY) || $remainingDaysCount <= 0) {
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
   * @return array List of weeks data. Structure:
   *   [
   *    "week-01" => [
   *      "days": [
   *        [
   *          "number": <int: Number in the month>,
   *          "month-label": <string: Name of the month>,
   *          "is-placeholder": <bool>,
   *          "is-current-day": <bool>,
   *          "is-first-of-month": <bool>,
   *        ],
   *        ...
   *      ],
   *      "tasks" : [
   *        [
   *          "text": <string>,
   *          "week-portion": <float: 0.0-100.0>,
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
