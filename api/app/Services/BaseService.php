<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionException;

/**
 * Classe abstrata BaseService
 *
 * Esta classe serve como base para todos os serviços da aplicação, fornecendo métodos padronizados
 * para operações CRUD e filtragem de dados. Implementa a camada de serviço no padrão de arquitetura
 * Repository-Service.
 */
abstract class BaseService
{
    /**
     * Repositório base associado ao serviço
     */
    protected BaseRepository $repository;

    /**
     * Requisição HTTP atual
     */
    protected Request $request;

    /**
     * Resultado da operação atual
     *
     * @var mixed
     */
    protected $result;

    /**
     * Array de dados a serem retornados após processamento
     */
    protected array $return = [];

    /**
     * Ordem em que os filtros devem ser aplicados
     */
    protected array $filtersOrder = [
        'query',
        'whereNull',
        'whereNotNull',
        'with',
        'withNotEmpty',
        'withEmpty',
        'hasRelationChildren',
        'orderBy',
        'orderByAsc',
        'orderByDesc',
        'paginated',
        'groupBy',
        'whereInColumn',
        'encrypted',
    ];

    /**
     * Construtor
     *
     * @param  BaseRepository  $repository  Repositório base para este serviço
     */
    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
        $this->filtersOrder = array_merge($this->repository->getGuarded(), $this->filtersOrder);
        $this->filtersOrder = array_merge($this->repository->getFillable(), $this->filtersOrder);
    }

    /**
     * Verifica se um array possui valores diferentes de string vazia
     *
     * @param  array  $array  Array a ser verificado
     * @return bool Retorna true se existirem valores não vazios
     */
    public function hasNonEmptyValues(array $array): bool
    {
        // Filtra o array, mantendo apenas valores não vazios
        $filteredArray = array_filter($array, function ($value) {
            return $value !== '';
        });

        // Se o array filtrado tiver elementos, então existem valores não vazios
        return ! empty($filteredArray);
    }

    /**
     * Compara IDs para identificar registros que precisam ser removidos
     *
     * Utilizado para operações de sincronização de relações
     *
     * @param  string  $model  Nome da tabela do modelo
     * @param  string  $key  Chave estrangeira
     * @param  int  $id  ID do registro principal
     * @param  array  $data  Dados com IDs a serem mantidos
     * @return array IDs que precisam ser removidos
     */
    public function compareIds(string $model, string $key, int $id, array $data): array
    {
        // Coleta os IDs que deseja manter
        $dataIds = array_map(function ($datum) {
            return $datum['id'];
        }, $data);

        // Coleta os ids atuais
        $modelIds = DB::table($model)
            ->where($key, $id)
            ->pluck('id')
            ->toArray();

        // Determina quais ids precisam ser deletadas
        return array_diff($modelIds, $dataIds);
    }

    /**
     * Formata dados paginados para retorno padronizado
     *
     * @param  mixed  $data  Resultado da paginação
     * @param  int  $limit  Limite de itens por página
     * @return array Array formatado com informações de paginação
     */
    public function getPaginate(mixed $data, int $limit): array
    {
        $count = $data->total();
        $currentPage = $data->currentPage();

        return [
            'data' => $data->items(),
            'currentPage' => $currentPage,
            'page' => $currentPage,
            'pages' => $data->lastPage(),
            'count' => $count,
            'per_page' => $limit,
        ];
    }

    /**
     * Retorna todos os registros do repositório
     *
     * @return array|Collection Todos os registros
     */
    public function all(): array|Collection
    {
        return $this->repository->all();
    }

    /**
     * Obtém registros com filtros e paginação aplicados
     *
     * @param  array|string  $columns  Colunas a serem selecionadas
     * @param  Request  $request  Requisição HTTP com parâmetros de filtro
     * @param  callable|null  $customQuery  Função para personalizar a query (opcional)
     * @param  string  $format  Formato de saída ('array' ou 'object')
     * @return array Dados filtrados e formatados
     */
    public function get(array|string $columns, Request $request, ?callable $customQuery = null, string $format = 'array'): array
    {
        if ($request->input('order')) {
            if ($request->input('order') === 'asc') {
                $request->merge(['orderByAsc' => $request->input('order_by')]);
            } else {
                $request->merge(['orderByDesc' => $request->input('order_by')]);
            }
        }
        $this->request = $request;

        $this->result = $this->repository->select($columns);

        $this->filtersOrder[] = 'created_at';
        foreach ($this->filtersOrder as $value) {
            $filter = $this->request->get($value);

            if (method_exists($this, $value) && ! empty($filter)) {
                $this->$value($filter);  // Aplicar filtro na query
            } else {
                if ($this->columnExists($value) && array_key_exists($value, $this->request->all()) && $this->request->input($value) !== null) {
                    $type = Schema::getColumnType($this->repository->getTable(), $value);
                    if (($type == 'datetime' || $type == 'date') && str_contains($filter, ',')) {
                        $this->whereBetweenDate($value, $filter);
                    } else {

                        if (is_array($filter)) {
                            $this->result->whereIn($value, $filter);
                        } else {
                            // Verifique se a model utiliza o pacote EncryptedAttribute e se o valor está encriptado
                            if (property_exists($this->repository->getModel(), 'encryptable') && in_array($value, $this->repository->getModel()->getEncryptable())) {
                                if (ctype_digit($filter)) {
                                    $this->result->whereEncrypted($value, $filter);
                                } else {
                                    $this->result->whereEncrypted($value, 'like', '%'.$filter.'%');
                                }
                            } else {
                                $this->where($value, $filter);
                            }
                        }
                    }
                }
            }
        }

        // Aplicar query personalizada
        if ($customQuery && is_callable($customQuery)) {
            $customQuery($this->result);
        }

        if (! empty($this->request->get('limit'))) {
            // Paginar
            $this->result = $this->result->paginate($this->request->get('limit'));

            // Calcular informações de paginação
            $this->return['page'] = $this->result->currentPage();
            $this->return['pages'] = $this->result->lastPage();
        } else {
            // Se não houver paginação, apenas execute a query e obtenha os dados como Collection
            $this->result = $this->result->get();
        }

        // Obter os dados no formato desejado (array ou objeto)
        $array = ($format === 'array') ? $this->result->toArray() : $this->result;
        $results = (isset($array['data'])) ? $array['data'] : $array;

        // Preencher os dados de retorno
        $this->return['data'] = (! empty($results['data'])) ? $results['data'] : $results;
        $this->return['count'] = $array['total'] ?? $this->result->count();
        $this->return['filter'] = $this->result->count();
        $this->return['per_page'] = $this->request->get('limit');

        return $this->return;
    }

    /**
     * Aplica filtro de intervalo de datas entre dois valores
     *
     * @param  string  $key  Nome da coluna
     * @param  string  $value  String com datas separadas por vírgula
     */
    private function whereBetweenDate(string $key, string $value): void
    {
        $value = explode(',', $value);
        $this->result = $this->result->where($key, '>=', $value[0])->where($key, '<=', $value[1]);
    }

    /**
     * Encontra um registro pelo ID
     *
     * @param  int  $id  ID do registro
     * @return mixed Registro encontrado
     */
    public function find($id): mixed
    {
        return $this->repository->find($id);
    }

    /**
     * Exibe um registro pelo ID (alias para find)
     *
     * @param  int  $id  ID do registro
     * @return mixed Registro encontrado
     */
    public function show($id): mixed
    {
        return $this->repository->find($id);
    }

    /**
     * Encontra registros pelos parâmetros fornecidos
     *
     * @param  array  $data  Parâmetros de busca
     * @return mixed Registros encontrados
     */
    public function findBy(array $data): mixed
    {
        return $this->repository->findBy($data);
    }

    /**
     * Cria um novo registro
     *
     * @param  array  $data  Dados do novo registro
     * @return mixed Registro criado
     */
    public function create(array $data): mixed
    {
        return $this->repository->create($data);
    }

    /**
     * Atualiza um registro existente
     *
     * @param  array  $data  Dados a serem atualizados
     * @param  int  $id  ID do registro
     * @return mixed Registro atualizado
     *
     * @throws NotFoundException Se o registro não for encontrado
     */
    public function update(array $data, int $id): mixed
    {
        $elem = $this->repository->find($id);
        if ($elem) {
            $elem->update($data);

            return $elem;
        }
        throw new NotFoundException('Informações inválidas!');
    }

    /**
     * Alterna o status de um registro (booleano)
     *
     * @param  int  $id  ID do registro
     */
    public function changeStatus(int $id): void
    {
        $elem = $this->repository->find($id);

        $elem->status = ! $elem->status;
        $elem->save();
    }

    /**
     * Remove um registro
     *
     * @param  int  $id  ID do registro
     * @return void Resultado da operação
     */
    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }

    /**
     * Remove um registro
     *
     * @param  int  $id  ID do registro
     * @return void Resultado da operação
     */
    public function changeActive(int $id): void
    {
        $this->repository->changeActive($id);
    }

    /**
     * Remove múltiplos registros
     *
     * @param  array  $ids  IDs dos registros
     * @return int Quantidade de registros removidos
     */
    public function bulkDelete(array $ids): int
    {
        return $this->repository->bulkDelete($ids);
    }

    /**
     * Altera o status ativo de múltiplos registros
     *
     * @param  array  $ids  IDs dos registros
     * @param  bool  $active  Novo status ativo
     * @return int Quantidade de registros atualizados
     */
    public function bulkChangeActive(array $ids, bool $active): int
    {
        return $this->repository->bulkChangeActive($ids, $active);
    }

    /**
     * Carrega relações do modelo
     *
     * @param  mixed  $value  Nome da relação ou array de relações
     *
     * @throws ReflectionException
     */
    private function with(mixed $value): void
    {
        $this->result = $this->repository->withRelationIfExists($this->result, $value);
    }

    /**
     * Carrega relações não vazias do modelo
     *
     * @param  mixed  $value  Nome da relação ou array de relações
     *
     * @throws ReflectionException
     */
    private function withNotEmpty(mixed $value): void
    {
        $this->result = $this->repository->withRelationIfNotEmpty($this->result, $value);
    }

    /**
     * Carrega relações vazias do modelo
     *
     * @param  mixed  $value  Nome da relação ou array de relações
     *
     * @throws ReflectionException
     */
    private function withEmpty(mixed $value): void
    {
        $this->result = $this->repository->withRelationEmpty($this->result, $value);
    }

    /**
     * Aplica ordenação baseada no parâmetro ascending da requisição
     *
     * @param  string  $value  Campo para ordenação
     */
    public function orderBy(string $value): void
    {
        if ($this->request->get('ascending')) {
            $this->result = $this->result->orderBy($value);

            return;
        }

        $this->result = $this->result->orderByDesc($value);
    }

    /**
     * Aplica ordenação ascendente
     *
     * @param  array|string  $value  Campo(s) para ordenação
     */
    private function orderByAsc(array|string $value): void
    {
        if (is_array($value)) {
            foreach ($value as $filter) {
                $this->result = $this->result->orderBy($filter);
            }

            return;
        }
        $this->result = $this->result->orderBy($value);
    }

    /**
     * Aplica ordenação descendente
     *
     * @param  array|string  $value  Campo(s) para ordenação
     */
    private function orderByDesc(array|string $value): void
    {
        if (is_array($value)) {
            foreach ($value as $filter) {
                $this->result = $this->result->orderByDesc($filter);
            }

            return;
        }
        $this->result = $this->result->orderByDesc($value);
    }

    /**
     * Verifica se uma coluna existe na tabela do modelo
     *
     * @param  string  $value  Nome da coluna
     * @return bool True se a coluna existir
     */
    private function columnExists(string $value): bool
    {
        return Schema::hasColumn($this->repository->getTable(), $value);
    }

    /**
     * Aplica filtro where na consulta
     *
     * Trata diferentes tipos de valores e suporta campos criptografados
     *
     * @param  string  $key  Nome da coluna
     * @param  mixed  $value  Valor para filtro
     */
    private function where(string $key, mixed $value): void
    {
        if (strpos($value, ',')) {
            $value = explode(',', $value);
        }

        $this->result = $this->result->where(function ($query) use ($key, $value) {
            if ($encryptedProperties = $this->request->get('encrypted')) {
                foreach ($encryptedProperties as $property) {
                    if ($property == $key) {
                        $query->whereEncrypted($key, 'LIKE', '%'.$value.'%');

                        return;
                    }
                }
            }

            if (is_array($value)) {
                $query->whereIn($key, $value);

                return;
            }
            if (is_numeric($value)) {
                $query->where($key, $value);
            } else {
                $query->whereRaw("LOWER({$key}) LIKE LOWER(?)", '%'.$value.'%');
            }
        });
    }

    /**
     * Aplica filtro whereNull (campo é nulo)
     *
     * @param  string  $key  Nome da coluna
     */
    private function whereNull(string $key): void
    {
        $this->result = $this->result->whereNull($key);
    }

    /**
     * Aplica filtro whereNotNull (campo não é nulo)
     *
     * @param  string  $key  Nome da coluna
     */
    private function whereNotNull(string $key): void
    {
        $this->result = $this->result->whereNotNull($key);
    }

    /**
     * Filtra registros que possuem relações filhas
     *
     * @param  string  $key  Nome da relação
     */
    private function hasRelationChildren(string $key): void
    {
        $this->result = $this->result->has($key);
    }

    /**
     * Aplica filtro de busca geral em todas as colunas textuais
     *
     * @param  string  $value  Termo de busca
     */
    private function query(string $value): void
    {
        $columns = $this->repository->getFillable();
        foreach ($columns as $column) {
            $type = Schema::getColumnType($this->repository->getTable(), $column);
            if (! in_array($type, ['integer', 'boolean', 'decimal'])) {
                $this->result = $this->result->orWhereRaw("LOWER({$column}) LIKE LOWER('%{$value}%')");
            } else {
                if (is_numeric($value) || is_bool($value)) {
                    $this->result = $this->result->orWhere($column, $value);
                }
            }
        }
    }

    /**
     * Agrupa resultados por uma coluna
     *
     * @param  string  $column  Nome da coluna
     */
    private function groupBy(string $column): void
    {
        $this->result = $this->result->groupBy($column);
    }

    /**
     * Aplica filtro whereIn em uma coluna específica
     *
     * Formato esperado: 'column[value1,value2,value3]'
     *
     * @param  string  $value  String formatada com coluna e valores
     */
    private function whereInColumn(string $value): void
    {
        $string = explode('[', $value);
        $column = $string[0];
        $value = substr($string[1], 0, -1);
        $value = explode(',', $value);
        $this->result = $this->result->whereIn($column, $value);
    }

    /**
     * Get available years for selection
     */
    public function getAvailableYears(): array
    {
        $startYear = $this->repository->getFirstYear();
        $latestYear = $this->repository->getLatestYear();

        return range($startYear, $latestYear);
    }
}
