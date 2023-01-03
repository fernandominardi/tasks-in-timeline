<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string id
 * @property string name
 * @property float position
 * @property string state
 * @property string checkListId
 */
class Task extends Model
{
  /** @param array $fieldList ["id", "name", "pos", "idChecklist", "state"] */
  function __construct($fieldList)
  {
    parent::__construct();
    $this->id           = $fieldList['id'];
    $this->name         = $fieldList['name'];
    $this->position     = $fieldList['pos'];
    $this->state        = $fieldList['state'];
    $this->checkListId  = $fieldList['idChecklist'];
  }
}
