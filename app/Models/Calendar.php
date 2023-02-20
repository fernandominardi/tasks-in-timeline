<?php

namespace App\Models;

use ArrayObject;
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
    $daysTotal = 0.0;
    foreach ($taskList as $id => $task) {
      $daysTotal += $task->daysEstimate;
    }
    $daysTotal;

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
      $remainingDaysCount = $daysTotal,
      $weekData = ["weekDays" => []];
      // Loop structure "while" condition
      !($remainingDaysCount <= 0 && $date->isDayOfWeek(Carbon::MONDAY));
      // Next loop initialization
      $date->addDays(1),
      $firstIteration = false
    ) {
      // We create and populate the basic day data.
      $dayData = [
        "dayNumber"       => $date->day,
        "monthName"       => $date->monthName,
        "isPlaceholder"   => false,
        "isNonWorkingDay" => false,
        "isCurrentDay"    => $date->isCurrentDay(),
        "showMonth"       => false,
        "remainingDaysCount" => $remainingDaysCount,
      ];

      // Check whether we have already reach the current day.
      if ($dayData["isCurrentDay"]) {
        $currentDayFound = true;
      }

      // Days after all tasks are finished are consider placeholders too
      if ($remainingDaysCount <= 0) {
        $dayData["isPlaceholder"] = true;
      }
      // Non-working days case
      // TODO: This can be improved by making it more general, instead of hardcoded.
      if( env('TASK_WORKING_DAYS_ON_WEEK') == 6 ){
        if ($date->isDayOfWeek(Carbon::SUNDAY)) {
          $dayData["isNonWorkingDay"] = true;
        }
      }elseif( env('TASK_WORKING_DAYS_ON_WEEK') == 5 ){
        if ($date->isDayOfWeek(Carbon::SUNDAY) || $date->isDayOfWeek(Carbon::SATURDAY) ) {
          $dayData["isNonWorkingDay"] = true;
        }
      }
      

      // If we have reached the current day we can start subtracting de remaining days
      // Otherwise we mark the day as a placeholder. 
      if ($currentDayFound && !$dayData["isNonWorkingDay"]) {
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
      $weekData["weekDays"][$date->dayOfWeekIso] = $dayData;

      // If it's the last day of the week, we add the completed week to the week list,
      // and initialize the next one.
      if ($date->isDayOfWeek(Carbon::SUNDAY) /*|| $remainingDaysCount <= 0*/) {
        $weekListData[] = $weekData;
        $weekData = ["weekDays" => []];
      }
    }

    // Return the already populated data.
    return $weekListData;
  }

  /**
   * It takes the $calendarData and populate with the task information needed in frontend. 
   * 
   * @param array   $calendarData Pass by reference calendar data.
   * @param Task[]|Collection  $taskList
   */
  static public function populateWithTasks(&$calendarData, $taskList)
  {
    // The goal is to add information about how tasks should be stacked on each week of the calendar.
    // The main loop iterates through calendar weeks. For the tasks however we use and iterator in 
    // order to traverse the task list in a conditional way while inside the main loop.
    $taskIterator = (new ArrayObject($taskList->all()))->getIterator();
    // This variable determines how much of the current task is left to add into the weeks.
    // We initialize as zero in order to for the extraction of the next (aka first) task.
    $taskRemainingPercentage = 0;

    $firstWeek = true;
    // Main loop: weeks inside the calendar data array.
    foreach ($calendarData as $weekIndex => $weekData) {

      // Initialization of the task data property.
      $calendarData[$weekIndex]["tasksData"] = [];
      // This variable determines how much of the week is available to use.
      $weekRemainingPercentage = round((100 / 7) * env('TASK_WORKING_DAYS_ON_WEEK'), 2);

      if ($firstWeek) {
        // Before adding actual tasks, we need to add some padding tasks
        // to fill the days in the week previous to de current day.
        $dayPortion = round(100 / 7, 2);
        foreach ($calendarData[$weekIndex]['weekDays'] as $dayData) {
          if ($dayData['isCurrentDay']) {
            break;
          }
          $calendarData[$weekIndex]["tasksData"][] = [
            "text" => '',
            "days" => null,
            "weekPortion" => $dayPortion,
            "isPlaceholder" => true,
          ];
          $weekRemainingPercentage -= $dayPortion;
        }

        $firstWeek = false;
      }

      // Task loop. We add tasks until there is not space in the week.
      // If a task is added just partiality in a particular week,
      // that same task is going to be used in the next week look.
      while ($weekRemainingPercentage > 1) {
        // Note: The while condition could be `> 0`, but we use `> 1` 
        // in case there is a small remainder due to float operations.

        if ($taskRemainingPercentage <= 1) {
          // Note: The if condition could be `<= 0`, but we use `<= 1` 
          // in case there is a small remainder due to float operations.

          // This is a safety in case the task list is used completely.
          if (!$taskIterator->valid()) {
            break;
          }

          // We get the next task and initialize a variable with
          // the length of the task in "calendar percentage" units. 
          /** @var Task $task */
          $task = $taskIterator->current();
          $taskRemainingPercentage = $task->daysInWeekPercentage;
          $taskIterator->next();
        }

        // We "fill" the current week subtracting all the task length
        // OR want is left in the week space, whatever is smaller.
        // (That is because we should not use more that is available in the week)
        $maxPortionToFill = min($taskRemainingPercentage, $weekRemainingPercentage);

        // Adding the task to the week.
        $calendarData[$weekIndex]["tasksData"][] = [
          "text" => $task->name,
          "days" => $task->daysEstimate,
          "weekPortion" => round($maxPortionToFill, 2),
          "isPlaceholder" => false,
        ];

        // Subtracting what has been added.
        $weekRemainingPercentage -= $maxPortionToFill;
        $taskRemainingPercentage -= $maxPortionToFill;
      }
    }
  }

  /**
   * @return array List of weeks data. Basic structure:
   *   [
   *    "week-01" => [
   *      "weekDays": [
   *        [
   *          "dayNumber": <int: Number in the month>,
   *          ...
   *        ],
   *        ...
   *      ],
   *      "weekTasks" : [
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
