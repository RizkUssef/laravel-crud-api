<?php

namespace Rizkussef\LaravelCrudApi\Http\Controllers;

use Illuminate\Http\Request;
use Rizkussef\LaravelCrudApi\Traits\ApiResponse;
use Illuminate\Support\Str;
use Rizkussef\LaravelCrudApi\Services\ApiCrudService;

class ApiCrudController
{
    use ApiResponse;
    protected string $form_request;
    protected string $update_request;
    protected ApiCrudService $service;
    public function __construct(ApiCrudService $service)
    {
        if (!isset($this->form_request))
            $this->form_request = $this->resolveFormRequest();

        if (!isset($this->update_request))
            $this->update_request = $this->resolveUpdateRequest();
        $this->service = $service;
    }
    protected function resolveEntityName(): string
    {
        $controllerName = (new \ReflectionClass($this))->getShortName();
        return Str::replaceLast('Controller', '', $controllerName);
    }
    protected function resolveFormRequest(): string
    {
        $form_request = $this->form_request ??
            "App\\Http\\Requests\\{$this->resolveEntityName()}Request";

        if (!class_exists($form_request))
            throw new \Exception("{$form_request} does not exist.");

        return $form_request;
    }
    protected function resolveUpdateRequest(): string
    {
        $update_request = $this->update_request ??
            "App\\Http\\Requests\\{$this->resolveEntityName()}UpdateRequest";

        if (!class_exists($update_request))
            return $this->form_request;

        return $update_request;
    }
    public function index(Request $request)
    {
        $filters = $request->query('filters', []);
        $relationships = $request->query('relationships', []);
        // Parse JSON if string
        if (is_string($filters)) {
            $filters = json_decode($filters, true) ?? [];
        }
        if (is_string($relationships)) {
            $relationships = json_decode($relationships, true) ?? [];
        }
        $data = $this->service->index($filters, $relationships);
        return $this->success($data, 'Data retrieved successfully', 200);
    }
    public function getPaginated(Request $request)
    {
        $per_page = $request->query('per_page', config('core-crud.paginate', 15));
        $filters = $request->query('filters', []);
        $relationships = $request->query('relationships', []);
        // Parse JSON if string
        if (is_string($filters)) {
            $filters = json_decode($filters, true) ?? [];
        }
        if (is_string($relationships)) {
            $relationships = json_decode($relationships, true) ?? [];
        }
        $data = $this->service->getPaginated($per_page, $filters, $relationships);
        return $this->success($data, 'Data retrieved successfully', 200);
    }
    public function store(Request $request)
    {
        $form_request = app($this->form_request);
        $form_request->validateResolved();
        $data = $form_request->validated();
        return $this->success($this->service->store($data), 'Data created successfully', 201);
    }
    public function show(Request $request, $id)
    {
        $filters = $request->query('filters', []);
        $relationships = $request->query('relationships', []);
        // Parse JSON if string
        if (is_string($filters)) {
            $filters = json_decode($filters, true) ?? [];
        }
        if (is_string($relationships)) {
            $relationships = json_decode($relationships, true) ?? [];
        }
        $data = $this->service->show($id, $filters, $relationships);
        return $this->success($data, 'Data retrieved successfully', 200);
    }
    public function update(Request $request, $id)
    {
        $update_request = app($this->update_request);
        $update_request->validateResolved();
        $data = $update_request->validated();
        return $this->success($this->service->update($id, $data), 'Data updated successfully', 200);
    }
    public function destroy($id)
    {
        return $this->success($this->service->destroy($id), 'Data deleted successfully', 200);
    }
}
