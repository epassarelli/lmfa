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

  public static function getNLast($model, $n, $orderColumn = 'id', $orderDirection = 'desc')
  {
    return $model::where('estado', 1)
      ->orderBy($orderColumn, $orderDirection)
      ->take($n)
      ->get();
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
    return $model::where('estado', 1)
      ->orderBy($visitColumn, 'desc')
      ->take($n)
      ->get();
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
    return $model::where($titleColumn, 'LIKE', $letter . '%')
      ->where('estado', 1)
      ->take($n)
      ->get();
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

  /**
   * Obtener contenidos relacionados a un intérprete.
   *
   * @param \App\Models\Interprete $interprete
   * @param string $seccion
   * @param \Illuminate\Database\Eloquent\Model $contenidoActual
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getRelatedContent($interprete, $seccion, $contenidoActual, $orderBy = 'id', $direction = 'desc')
  {
    $relationMethod = $this->getRelationMethod($seccion);

    if (method_exists($interprete, $relationMethod)) {
      return $interprete->$relationMethod()
        ->where('estado', 1)
        ->where('id', '!=', $contenidoActual->id)
        ->orderBy($orderBy, $direction)
        ->get();
    }

    return new Collection();
  }
  /**
   * Obtener el método de relación basado en la sección.
   *
   * @param string $seccion
   * @return string
   */
  private function getRelationMethod($seccion)
  {
    switch ($seccion) {
      case 'noticias':
        return 'noticias';
      case 'discos':
        return 'discos';
      case 'canciones':
        return 'canciones';
      case 'shows':
        return 'shows';
      default:
        throw new \InvalidArgumentException("Sección no válida: {$seccion}");
    }
  }
}
