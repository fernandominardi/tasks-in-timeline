<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

/**
 * @property string $id
 * @property string $name Task description single line text.
 * @property float  $position Position as provided by Trello API, note that it can be float and that the sequence is not predictable.
 * @property string $isComplete
 * @property string $checkListId
 * @property float  $daysEstimate Custom field.
 * @property float  $daysInWeekPercentage Custom field.
 */
class Task extends Model
{
  /** 
   * @param array $fieldList Fields as provided by Trello API: "id", "name", "pos", "idChecklist", "state". 
   * */
  function __construct($fieldList)
  {
    parent::__construct();
    $this->checkItemId  = $fieldList['id'];
    $this->name         = $fieldList['name'];
    $this->position     = $fieldList['pos'];
    $this->isComplete   = $fieldList['state'] == 'complete';
    $this->checkListId  = $fieldList['idChecklist'];
    $this->daysEstimate = null;
    $this->daysInWeekPercentage = null;
    $this->weekPercentageCount = null;

    if ($this->isComplete) {
      return;
    }

    // There is an accepted nomenclature on which the user can specify the remaining time of the task
    // by writing inside curly brackets the number of days followed by the letter "d" or hours followed by 
    // the letter "h" (decimals permitted in both cases). So we search that data using regular expressions. 
    preg_match_all('/\{(.*?)\}/', $this->name, $matchData);
    $allMatches = $matchData[1];

    // We get the value, or set a default value in case none is found.
    $timeData = !$allMatches ? env('TASK_DEFAULT_REMAINING_TIME') : $allMatches[0];

    // Extract parts of the input and make some validations.
    $numericPart = rtrim($timeData, 'dh');
    $dayOrHourIndicator = substr($timeData, -1);

    if (($dayOrHourIndicator != 'd' && $dayOrHourIndicator != 'h') || !is_numeric($numericPart)) {
      abort(403, "Error: Invalid time remaining provided for task: \"{$this->name}\" ({$timeData}) -> {$numericPart}|{$dayOrHourIndicator}");
      echo '<br>';
    }

    // We assign the days directly or made a conversion from hours if needed.
    if ($dayOrHourIndicator == 'd') {
      $this->daysEstimate = (float) $numericPart;
    } elseif ($dayOrHourIndicator == 'h') {
      $this->daysEstimate = round($numericPart / env('TASK_EFFECTIVE_HRS_IN_DAY'), 2);
    }

    // We calculate the duration in terms of week proportion (percentage).
    $this->daysInWeekPercentage = precision_floor($this->daysEstimate * (100 / 7), 2);
    $this->weekPercentageCount = $this->weekPercentageCount;
  }

  /**
   * Provides the contents of a particular Checklist in the form of a Collection of Tasks.
   * TODO: For now this method uses trello IDs that you need to know in advance and set them in the `.env` file, 
   * this can be improved so that, ideally, you don't need to make manual API calls to check the desired IDs.
   *  
   * @param bool $excludeCompleted
   * @return Task[]|Collection
   */
  public static function getTaskList($excludeCompleted = false)
  {
    $checklistsOnCardData = Task::getChecklistsOnCard(env('TRELLO_TARGET_CARD_ID'), ['checkItem_fields' => 'name,pos,state']);
    $taskList = Task::extractItemsByChecklistId($checklistsOnCardData, env('TRELLO_TARGET_CHECKLIST_ID'), $excludeCompleted);

    // Deleting all tasks at and after the "-- end of list --" was found.
    // TODO: This process can be optimized.
    $endListMarkerFound = false;
    foreach( $taskList as $key => $task ){
      if( !$endListMarkerFound && $task->name == "-- end of list --" ){
        $endListMarkerFound = true;
      }
      if( $endListMarkerFound ){
        unset($taskList[$key]);
      }
    }

    return $taskList;
  }

  /**
   * This method is just a helper to make the a particular trello API call.
   * It returns the raw data of Checklists belonging to a particular Card.
   * 
   * @param string  $cardId
   * @param array   $queryParameters
   * @return array
   */
  private static function getChecklistsOnCard($cardId, $queryParameters)
  {
    $getVariables = new Collection([
      'key' => env('TRELLO_KEY'),
      'token' => env('TRELLO_TOKEN'),
    ]);

    $getVariables = $getVariables->merge($queryParameters);

    $getVariablesStr = $getVariables->map(function ($value, $key) {
      return "{$key}={$value}";
    })->implode('&');

    $response = Http::get("https://api.trello.com/1/cards/{$cardId}/checklists?{$getVariablesStr}");

    return $response->json();
  }

  /**
   * This method returns a Collection of sorted Tasks objects, based on the raw
   * Checklists data ( as provided by trello API) and a target Checklist ID.
   * 
   * @param array   $checklistsOnCardData
   * @param string  $checklistId
   * @param bool    $excludeCompleted
   * @return Task[]|Collection
   */
  private static function extractItemsByChecklistId($checklistsOnCardData, $checklistId, $excludeCompleted = false)
  {
    $targetChecklistData = [];
    foreach ($checklistsOnCardData as $checklistData) {
      if ($checklistData['id'] == $checklistId) {
        $targetChecklistData = $checklistData['checkItems'];
      }
    }

    $taskList = new Collection();
    foreach ($targetChecklistData as $checkItemData) {
      if (!$excludeCompleted || $checkItemData['state'] != 'complete') {
        $taskList[$checkItemData['id']] = new Task($checkItemData);
      }
    }

    return $taskList->sortBy('position');
  }
}
