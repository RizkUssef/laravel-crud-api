<?php

namespace Rizkussef\LaravelCrudApi\Services;

use Illuminate\Support\Str;
use Rizkussef\LaravelCrudApi\Traits\FilterQueryTrait;
use Rizkussef\LaravelCrudApi\Traits\RelationshipsQueryTrait;

class ApiCrudService
{
    use FilterQueryTrait;
    use RelationshipsQueryTrait;
    protected $model;
    public function __construct()
    {
        if (!isset($this->model)) {
            $this->model = $this->resolveModel();
        }
    }
    /**
     * Guess Model class based on current service name
     */
    public function resolveModel()
    {
        $service_name = (new \ReflectionClass($this))->getShortName(); // e.g. UserService
        $model_name = Str::replaceLast('Service', '', $service_name); // → Post
        $model_class = "App\\Models\\{$model_name}";
        if (!class_exists($model_class)) {
            throw new \Exception("Model {$model_class} does not exist.");
        }

        return new $model_class;
    }
    /**
     * Guess Resource class based on current service name
     */
    protected function resolveResource(): string|null
    {
        $service_name = (new \ReflectionClass($this))->getShortName(); // e.g. UserService
        $resource_name = Str::replaceLast('Service', 'Resource', $service_name); // → UserResource
        $resource_class = "App\\Http\\Resources\\{$resource_name}";

        if (!class_exists($resource_class)) {
            // throw new \Exception("Resource {$resource_class} does not exist.");
            return null;
        }
        return $resource_class;
    }
    /**
     * Apply resource to data
     */
    protected function applyResource($data, bool $is_collection = false): mixed
    {
        $resource_class = $this->resolveResource();
        if ($resource_class === null) {
            return $data;
        } else {
            if ($is_collection) {
                return $resource_class::collection($data);
            }

            return new $resource_class($data);
        }
    }
    public function index($filters = [], $relationships = [])
    {
        $query = $this->model->newQuery();
        $query = $this->applyFilters($query, $filters);
        $query = $this->applyRelationships($query, $relationships);
        $data = $query->get();
        return $this->applyResource($data, true);
    }
    public function getPaginated($perPage = 15, $filters = [], $relationships = [])
    {
        $query = $this->model->newQuery();
        $query = $this->applyFilters($query, $filters);
        $query = $this->applyRelationships($query, $relationships);
        $data = $query->paginate($perPage);
        return $this->applyResource($data, true);
    }
    public function store($data)
    {
        $data = $this->model->create($data);
        return $this->applyResource($data);
    }
    public function show($id,  $filters = [], $relationships = [])
    {
        $query = $this->model->newQuery();
        $query = $this->applyFilters($query, $filters);
        $query = $this->applyRelationships($query, $relationships);
        $data = $query->findOrFail($id);
        return $this->applyResource($data);
    }
    public function update($id, $data)
    {
        $record = $this->model->findOrFail($id);
        return $record->update($data);
    }
    public function destroy($id)
    {
        $record = $this->model->findOrFail($id);
        return $record->delete();
    }
}
