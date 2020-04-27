<?php

namespace Wptomo\Hane;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Wptomo\Hane\Exceptions\MethodNotExistsException;
use Wptomo\Hane\Exceptions\PropertyNotExistsException;

abstract class Converter
{
    const DATA_TYPE_RAW = 0;
    const DATA_TYPE_COLLECTION = 1;
    const DATA_TYPE_MODEL = 2;

    /**
     * Original data.
     *
     * @var array|Model|Collection
     */
    protected $rawData;

    /**
     * Request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * Array keys which you want to keep.
     *
     * @var array
     */
    protected $only;

    /**
     * Wrap key.
     *
     * @var string|null
     */
    protected $wrapKey;

    /**
     * Attach data.
     *
     * @var array
     */
    protected $attach;

    /**
     * Include data.
     *
     * @var array
     */
    protected $include = [];

    /**
     * Some method can execute after data conversion.
     *
     * @var null
     */
    protected $shouldCallback = null;

    /**
     * Type of raw data.
     *
     * @var int
     */
    protected $dataType = self::DATA_TYPE_RAW;

    /**
     * ConverterContract constructor.
     *
     * @param array|Model|Collection $data
     * @param array $only
     * @param string $wrapKey
     * @param array $attach
     */
    public function __construct($data, $only = [], $wrapKey = null, $attach = [])
    {
        $this->rawData = $data;
        $this->request = request();
        $this->only = $only;
        $this->wrapKey = $wrapKey;
        $this->attach = $attach;
    }

    /**
     * Convert data.
     *
     * @return array
     *
     * @throws MethodNotExistsException
     */
    public function convert()
    {
        $converted = $this->rawData;

        if ($this->rawData instanceof Collection) {
            $this->dataType = self::DATA_TYPE_COLLECTION;
            $converted = $this->convertCollection($this->rawData);
        }

        if ($this->rawData instanceof Model) {
            $this->dataType = self::DATA_TYPE_MODEL;
            $converted = $this->realThing($this->rawData);
        }

        if ($this->wrapKey !== null) {
            $converted = [$this->wrapKey => $converted];

            if (count($this->attach)) {
                $converted = array_merge($converted, $this->attach);
            }
        }

        if ($this->shouldCallback) {
            $this->{$this->shouldCallback}($converted, $this->dataType);
        }

        return $converted;
    }

    /**
     * Handle collection case.
     *
     * @param Collection $collection
     * @return array
     */
    private function convertCollection(Collection $collection)
    {
        // If user need collection type of Key-Value, add 'collection_key' param to request url.
        if ($this->request->has('collection_key')) {
            $key = $this->request->input('collection_key');

            return $collection->mapWithKeys(function ($item) use ($key) {
                if (
                    ! isset($item->{$key})
                    || $item->{$key} === null
                    || $item->{$key} === ''
                ) {
                    throw new PropertyNotExistsException("The collection key '{$key}' in item is not exists.");
                }

                return [$item->{$key} => $this->realThing($item)];
            })->toArray();
        }

        return $collection->map(function ($item) {
            return $this->realThing($item);
        })->toArray();
    }

    /**
     * Handle model data.
     *
     * @param Model $model
     * @return array
     *
     * @throws MethodNotExistsException
     */
    private function realThing(Model $model)
    {
        $realThing = count($this->only)
            ? Arr::only($this->toArray($model), $this->only)
            : $this->toArray($model);

        $shouldInclude = $this->processInclude($model);

        if (count($shouldInclude)) {
            $realThing = array_merge($realThing, $shouldInclude);
        }

        return $realThing;
    }

    /**
     * Process include data if current request has 'include' param.
     *
     * @param $model
     * @return array
     *
     * @throws MethodNotExistsException
     */
    private function processInclude($model)
    {
        $shouldInclude = [];

        if ($this->request->has('include') && count($this->include)) {

            $includes = @explode(',', trim($this->request->include));

            foreach ($includes as $include) {
                if (in_array($include, $this->include)) {
                    $shouldInclude[Str::snake($include)] = $this->getIncludeConverter($include, $model)->convert();
                }
            }
        }

        return $shouldInclude;
    }

    /**
     * Get include param converter.
     *
     * @param $include
     * @param $model
     * @return Converter
     *
     * @throws MethodNotExistsException
     */
    private function getIncludeConverter($include, $model)
    {
        $method = 'include' . Str::studly($include);

        try {
            return $this->{$method}($model);
        } catch (Exception $e) {
            throw new MethodNotExistsException(
                sprintf('Method %s not exists in %s.', $method, $e->getFile())
            );
        }
    }

    /**
     * Subclass implementation.
     *
     * @param Model $model
     * @return array
     */
    abstract public function toArray(Model $model): array;
}
