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
    $taskList = Task::getTaskList(true);
    return view('task.home', compact('taskList'));
  }
}
