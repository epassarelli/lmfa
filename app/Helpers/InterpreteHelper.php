<?php

namespace App\Helpers;

use App\Models\Interprete;

class InterpreteHelper
{
  public static function getInterpretesByUserId($user_id)
  {
    $interpretes = Interprete::where('user_id', $user_id)->get();

    return $interpretes->toArray();
  }
}
