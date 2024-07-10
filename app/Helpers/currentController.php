<?php

use Illuminate\Support\Facades\Route;

if (!function_exists('currentController')) {
  function currentController()
  {
    $route = Route::getCurrentRoute();
    return $route ? class_basename($route->getController()) : null;
  }
}
