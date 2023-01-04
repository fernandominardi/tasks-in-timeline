<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

/**
 * @property string id
 * @property string name
 * @property float position
 * @property string isComplete
 * @property string checkListId
 */
class Task extends Model
{
  /** @param array $fieldList ["id", "name", "pos", "idChecklist", "state"] */
  function __construct($fieldList)
  {
    parent::__construct();
    $this->checkItemId  = $fieldList['id'];
    $this->name         = $fieldList['name'];
    $this->position     = $fieldList['pos'];
    $this->isComplete   = $fieldList['state']=='complete';
    $this->checkListId  = $fieldList['idChecklist'];
  }

  public static function getTaskList()
  {
    $checklistsOnCardData = Task::getChecklistsOnCard(env('TRELLO_TARGET_CARD_ID'), ['checkItem_fields' => 'name,pos,state']);
    $taskList = Task::extractItemsByChecklistId($checklistsOnCardData, env('TRELLO_TARGET_CHECKLIST'));

    return $taskList;
  }

  /**
   * @param string $cardId
   * @param array $queryParameters
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
   * @param array $checklistsOnCardData
   * @param string $checklistId
   * @return Task[]|Collection
   */
  private static function extractItemsByChecklistId($checklistsOnCardData, $checklistId)
  {
    $targetChecklistData = [];
    foreach ($checklistsOnCardData as $checklistData) {
      if ($checklistData['id'] == $checklistId) {
        $targetChecklistData = $checklistData['checkItems'];
      }
    }

    $taskList = new Collection();
    foreach ($targetChecklistData as $checkItemData) {
      $taskList[$checkItemData['id']] = new Task($checkItemData);
    }

    $taskList = $taskList->sortBy('position');

    return $taskList;
  }
}
