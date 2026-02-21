<?php

namespace App\Repositories;

use App\Exceptions\NotFoundException;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Classe abstrata BaseRepository
 *
 * Esta classe implementa o padrão Repository para acesso a dados,
 * fornecendo uma interface padronizada para interações com o banco de dados.
 * Serve como base para todos os repositórios da aplicação, encapsulando a lógica
 * de persistência e consulta.
 */
abstract class BaseRepository
{
    /**
     * Modelo Eloquent associado ao repositório
     */
    protected Model $model;

    /**
     * Construtor do BaseRepository
     *
     * @param Model $model Instância do modelo Eloquent
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Retorna todos os registros do modelo
     *
     * @return Collection|Model[] Coleção de todos os registros
     */
    public function all(): array|Collection
    {
        return $this->model->all();
    }

    /**
     * Retorna a instância do modelo
     *
     * @return Model Instância do modelo Eloquent
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Encontra um registro pelo ID
     *
     * @param int $id ID do registro
     * @return mixed Registro encontrado ou null
     *
     * @throws NotFoundException
     */
    public function find(int $id): mixed
    {
        $el = $this->model->find($id);

        if (!$el) {
            throw new NotFoundException('Item não encontrado');
        }

        return $el;
    }

    /**
     * Encontra um registro pelo ID
     *
     * @param int $id ID do registro
     */
    public function changeActive(int $id): void
    {
        $model = $this->model->find($id);

        if (!$model) {
            throw new NotFoundHttpException('Informações inválidas!');
        }

        $model->active = !$model->active;
        $model->save();
    }

    /**
     * Encontra um registro pelos parâmetros fornecidos
     *
     * Suporta comparações simples e listas (whereIn)
     *
     * @param array $data Parâmetros de busca no formato [campo => valor]
     * @return mixed Primeiro registro encontrado ou null
     */
    public function findBy(array $data): mixed
    {
        $query = $this->model->newQuery();
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $query = $query->whereIn($key, $value);
            } else {
                $query = $query->where($key, $value);
            }
        }

        return $query->first();
    }

    /**
     * Encontra registros por intervalo de datas
     *
     * @param string $key Nome da coluna de data
     * @param array $value Array com data inicial e final [inicio, fim]
     * @return mixed Coleção de registros
     */
    public function whereBetween(string $key, array $value): mixed
    {
        return $this->model::whereBetween($key, $value)->get();
    }

    /**
     * Encontra registros com relações carregadas
     *
     * @param array $relations Nomes das relações a serem carregadas
     * @param int|null $id ID do registro (opcional)
     * @return Collection Registro(s) com relações
     */
    public function with(array $relations, ?int $id = null): Collection
    {
        if ($id) {
            return $this->model::with($relations)->where('id', $id)->first();
        }

        return $this->model::with($relations)->get();
    }

    /**
     * Inicia uma consulta com colunas específicas
     *
     * @param string|array $columns Colunas a serem selecionadas
     * @return mixed Query builder com select aplicado
     */
    public function select($columns = '*'): mixed
    {
        return $this->model->select($columns);
    }

    /**
     * Cria um novo registro
     *
     * @param array $data Dados do novo registro
     * @return mixed Registro criado
     */
    public function create(array $data): mixed
    {
        return $this->model->create($data);
    }

    /**
     * Obtém o primeiro registro que atende às condições ou cria um novo
     *
     * @param array $condition Condições para busca
     * @param array $data Dados para criação se não encontrado
     * @return mixed Registro encontrado ou criado
     */
    public function firstOrCreate(array $condition, array $data): mixed
    {
        return $this->model->firstOrCreate($condition, $data);
    }

    /**
     * Obtém o primeiro registro que atende às condições ou cria uma instância não persistida
     *
     * @param array $condition Condições para busca
     * @param array $data Dados para nova instância se não encontrado
     * @return mixed Registro encontrado ou nova instância
     */
    public function firstOrNew(array $condition, array $data): mixed
    {
        return $this->model->firstOrNew($condition, $data);
    }

    /**
     * Atualiza um registro existente
     *
     * @param array $data Dados para atualização
     * @param int $id ID do registro
     * @return mixed Resultado da operação de atualização ou null
     */
    public function update(array $data, int $id): mixed
    {
        $model = $this->model->find($id);

        return $model ? $model->update($data) : null;
    }

    /**
     * Remove um registro
     *
     * @param int $id ID do registro
     * @return mixed Resultado da operação de remoção
     */
    public function delete(int $id): mixed
    {
        return $this->model->find($id)?->delete();
    }

    /**
     * Remove múltiplos registros
     * Deleta individualmente para disparar eventos do Eloquent (observers)
     *
     * @param array $ids IDs dos registros
     * @return int Quantidade de registros removidos
     */
    public function bulkDelete(array $ids): int
    {
        $count = 0;
        $models = $this->model->whereIn('id', $ids)->get();

        foreach ($models as $model) {
            if ($model->delete()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Altera o status ativo de múltiplos registros
     *
     * @param array $ids IDs dos registros
     * @param bool $active Novo status ativo
     * @return int Quantidade de registros atualizados
     */
    public function bulkChangeActive(array $ids, bool $active): int
    {
        return $this->model->whereIn('id', $ids)->update(['active' => $active]);
    }

    /**
     * Retorna a contagem total de registros
     *
     * @return int Número de registros
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Retorna os atributos protegidos do modelo
     *
     * @return array Lista de atributos protegidos
     */
    public function getGuarded(): array
    {
        return $this->model->getGuarded();
    }

    /**
     * Retorna os atributos preenchíveis do modelo
     *
     * @return array Lista de atributos preenchíveis
     */
    public function getFillable(): array
    {
        return $this->model->getFillable();
    }

    /**
     * Retorna o nome da tabela do modelo
     *
     * @return string Nome da tabela
     */
    public function getTable(): string
    {
        return $this->model->getTable();
    }

    /**
     * Carrega relações se existirem no modelo
     *
     * @param mixed $query Query builder
     * @param mixed $relations Nome(s) da(s) relação(ões)
     * @return mixed Query builder com relações carregadas
     *
     * @throws ReflectionException
     */
    public function withRelationIfExists(mixed $query, mixed $relations): mixed
    {
        return $this->filterRelations($query, $relations);
    }

    /**
     * Carrega relações se existirem e não estiverem vazias
     *
     * @param mixed $query Query builder
     * @param mixed $relations Nome(s) da(s) relação(ões)
     * @return mixed Query builder com relações filtradas
     *
     * @throws ReflectionException
     */
    public function withRelationIfNotEmpty(mixed $query, mixed $relations): mixed
    {
        return $this->filterRelations($query, $relations, true);
    }

    /**
     * Filtra registros que não possuem relações (estão vazias)
     *
     * @param mixed $query Query builder
     * @param mixed $relations Nome(s) da(s) relação(ões)
     * @return mixed Query builder com filtro aplicado
     *
     * @throws ReflectionException
     */
    public function withRelationEmpty(mixed $query, mixed $relations): mixed
    {
        return $this->filterRelations($query, $relations, false, true);
    }

    /**
     * Método auxiliar para filtrar relações dinamicamente
     *
     * Permite carregar relações específicas e aplicar filtros com base
     * na existência ou não de dados relacionados.
     *
     * @param mixed $query Query builder
     * @param mixed $relations Nome(s) da(s) relação(ões)
     * @param bool $notEmpty Indica se deve filtrar registros que têm relações
     * @param bool $empty Indica se deve filtrar registros que não têm relações
     * @param bool $loadOnly Indica se deve apenas carregar relações sem filtrar
     * @return mixed Query builder com filtros aplicados
     *
     * @throws ReflectionException
     */
    private function filterRelations(mixed $query, mixed $relations, bool $notEmpty = false, bool $empty = false, bool $loadOnly = false): mixed
    {
        $reflection = new \ReflectionClass($this->model);
        $allRelations = array_column($reflection->getMethods(), 'name');
        //        dd('che');

        if (is_array($relations)) {
            foreach ($relations as $relation) {
                $columns = [];
                if (str_contains($relation, ':')) {
                    $arr = explode(':', $relation);
                    $relation = $arr[0];
                    $columns = str_contains($arr[1], ',') ? explode(',', $arr[1]) : $arr[1];
                }
                if (!in_array($relation, $allRelations) && !str_contains($relation, '.')) {
                    continue;
                }

                // Filtra registros que não têm a relação
                if ($empty) {
                    $query->doesnthave($relation);
                } // Filtra para incluir apenas registros que têm a relação (se não for apenas carregamento)
                elseif ($notEmpty && !$loadOnly) {
                    $query->has($relation);

                    // Também carrega a relação com colunas específicas se necessário
                    if (empty($columns)) {
                        $query->with($relation);
                    } else {
                        $query->with([
                            $relation => function ($q) use ($columns) {
                                $q->select($columns);
                            },
                        ]);
                    }
                } // Apenas carrega as relações sem filtrar
                else {
                    if (empty($columns)) {
                        $query->with($relation);
                    } else {
                        $query->with([
                            $relation => function ($q) use ($columns) {
                                $q->select($columns);
                            },
                        ]);
                    }
                }
            }
        } else {
            // Manipulação para uma única relação (não array)
            $columns = [];
            if (str_contains($relations, ':')) {
                $arr = explode(':', $relations);
                $relations = $arr[0];
                $columns = str_contains($arr[1], ',') ? explode(',', $arr[1]) : $arr[1];
            }

            if (!in_array($relations, $allRelations) && !str_contains($relations, '.')) {
                return $query;
            }

            // Filtra registros que não têm a relação
            if ($empty) {
                $query->doesnthave($relations);
            } // Filtra para incluir apenas registros que têm a relação (se não for apenas carregamento)
            elseif ($notEmpty && !$loadOnly) {
                $query->has($relations);

                // Também carrega a relação com colunas específicas se necessário
                if (empty($columns)) {
                    $query->with($relations);
                } else {
                    $query->with([
                        $relations => function ($q) use ($columns) {
                            $q->select($columns);
                        },
                    ]);
                }
            } // Apenas carrega as relações sem filtrar
            else {
                if (empty($columns)) {
                    $query->with($relations);
                } else {
                    $query->with([
                        $relations => function ($q) use ($columns) {
                            $q->select($columns);
                        },
                    ]);
                }
            }
        }

        return $query;
    }

    /**
     * Aplica filtro em tabelas relacionadas
     *
     * Método utilizado apenas no repositório abstrato para filtrar
     * registros com base em condições em tabelas filhas.
     *
     * @param mixed $query Query builder
     * @param string $key Nome da coluna
     * @param mixed $value Valor para filtro
     * @param string|null $relation Nome da relação (opcional)
     * @return mixed Query builder com filtro aplicado
     */
    private function childrenWhere(mixed $query, string $key, mixed $value, ?string $relation = null): mixed
    {
        return $query->where(function ($subquery) use ($query, $key, $value, $relation) {
            if (is_array($value)) {
                foreach ($value as $column => $condition) {
                    $subquery->whereRaw("LOWER({$key}) LIKE LOWER(?)", '%' . $condition . '%');
                }

                return $query;
            }

            if ($relation) {
                try {
                    $relationModel = $this->model->{$relation}(); // Returns a Relations subclass
                    $relatedModel = $relationModel->getRelated(); // Returns a new empty Model
                    $tableName = $relatedModel->getTable();

                    $type = DB::connection()->getDoctrineColumn($tableName, $key)->getType()->getName();

                } catch (Exception $e) {
                    $type = null;
                }

                if (isset($type) && $type == 'integer') {
                    $subquery->whereRaw($key, $value);
                } else {
                    $subquery->whereRaw("LOWER({$key}) LIKE LOWER(?)", '%' . $value . '%');
                }
            }
        });
    }

    /**
     * Get the latest year with data
     */
    public function getLatestYear(): ?int
    {
        return $this->model->max('year');
    }

    /**
     * Get the first year with data
     */
    public function getFirstYear(): ?int
    {
        return $this->model->min('year');
    }

    public function findByIds(array $ids): Collection
    {
        return $this->model->whereIn('id', $ids)->get();
    }
}
