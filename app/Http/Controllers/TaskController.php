<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
  /**
   * @return \Illuminate\View\View
   */
  public function home()
  {
    $taskList = [
      new Task([
        "id" => 'test-id',
        "name" => 'test-name',
        "pos" => 1,
        "idChecklist" => 'test-list-id',
        "state" => 'test-state'
      ]),
      new Task([
        "id" => 'test-id',
        "name" => 'test-name',
        "pos" => 1,
        "idChecklist" => 'test-list-id',
        "state" => 'test-state'
      ]),
    ];
    return view('task.home', compact('taskList'));
  }
}
