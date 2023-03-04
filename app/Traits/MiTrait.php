<?php

namespace App\Traits;

trait MiTrait
{

  public function order($sort)
  {
    if ($this->sort == $sort) {

      if ($this->order == 'desc') {
        $this->order = 'asc';
      } else {
        $this->order = 'desc';
      }
    } else {
      $this->sort = $sort;
      $this->order = 'asc';
    }
  }
}
