<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
  /**
   * @return \Illuminate\View\View
   */
  public function home()
  {
    $calendarData = Calendar::getCalendarData();
    return view('task.home', compact('calendarData'));
  }
}
