<?php

namespace App\Traits;

use App\Models\Interprete;
// use App\Models\Noticia;
// use App\Models\Show;


trait CommonMethodsTrait
{

  /**
   * Get the last N records in a specified order.
   *
   * @param string $model
   * @param int $n
   * @param string $orderColumn
   * @param string $orderDirection
   * @return \Illuminate\Database\Eloquent\Collection
   */

  public static function getNLast($model, $n, $orderColumn = 'created_at', $orderDirection = 'desc')
  {
    return $model::orderBy($orderColumn, $orderDirection)->take($n)->get();
  }

  /**
   * Get the top N most visited records.
   *
   * @param string $model
   * @param int $n
   * @param string $visitColumn
   * @return \Illuminate\Database\Eloquent\Collection
   */

  public static function getNMostVisited($model, $n, $visitColumn = 'visitas')
  {
    return $model::orderBy($visitColumn, 'desc')->take($n)->get();
  }

  /**
   * Get the top N records whose title starts with a specific letter.
   *
   * @param string $model
   * @param int $n
   * @param string $letter
   * @param string $titleColumn
   * @return \Illuminate\Database\Eloquent\Collection
   */

  public static function getNStartsWith($model, $n, $letter, $titleColumn = 'title')
  {
    return $model::where($titleColumn, 'LIKE', $letter . '%')->take($n)->get();
  }

  /**
   * Search records based on a search term in a specific column.
   *
   * @param string $model
   * @param string $searchTerm
   * @param string $searchColumn
   * @return \Illuminate\Database\Eloquent\Collection
   */

  // public static function search($model, $searchTerm, $searchColumn)
  // {
  //   return $model::where($searchColumn, 'LIKE', '%' . $searchTerm . '%')->get();
  // }


  /**
   * Search records based on a search term in specific columns.
   *
   * @param string $model
   * @param string $searchTerm
   * @param array $searchColumns
   * @return \Illuminate\Database\Eloquent\Collection|string
   */
  public static function search($model, $searchTerm, array $searchColumns)
  {
    $query = $model::query();

    foreach ($searchColumns as $column) {
      $query->orWhere($column, 'LIKE', '%' . $searchTerm . '%');
    }

    $results = $query->get();

    return $results->isEmpty() ? 'No se encontraron resultados.' : $results;
  }

  // Métodos para traer objetos relacionados a los interpretes

  public function getInterpreteNoticias(Interprete $interprete)
  {
    return $interprete->noticias()->get();
  }

  public function getInterpreteShows(Interprete $interprete)
  {
    return $interprete->shows()->get();
  }
}
