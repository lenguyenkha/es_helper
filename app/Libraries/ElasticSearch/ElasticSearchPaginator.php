<?php

namespace App\Libraries\ElasticSearch;

use Illuminate\Pagination\LengthAwarePaginator;

class ElasticSearchPaginator extends LengthAwarePaginator
{
    /**
     * Option parameters
     *
     * @var array $options Option params
     */
    protected $options;

    /**
     * Create a new paginator instance.
     *
     * @param mixed    $items       Data items
     * @param int      $total       Total items
     * @param int      $perPage     How many items of per page
     * @param int|null $currentPage The number of current page
     * @param array    $options     (path, query, fragment, pageName)
     *
     * @return void
     */
    public function __construct($items, $total, $perPage, $currentPage = null, array $options = [])
    {
        parent::__construct($items, $total, $perPage, $currentPage, $options);
        $this->options = $options;
        $this->setQuery();
        $this->setPathPagination();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'meta' => [
                'current_page' => $this->currentPage(),
                'first_page_url' => $this->url(1),
                'from' => $this->firstItem(),
                'last_page' => $this->lastPage(),
                'last_page_url' => $this->url($this->lastPage()),
                'next_page_url' => $this->nextPageUrl(),
                'path' => $this->path(),
                'per_page' => $this->perPage(),
                'prev_page_url' => $this->previousPageUrl(),
                'to' => $this->lastItem(),
                'total' => $this->total(),
            ],
            'items' => $this->items->toArray()
        ];
    }

    /**
     * Set the path
     *
     * @return void
     */
    private function setQuery()
    {
        $this->query = $this->options['query'];
    }

    /**
     * Set the path
     *
     * @return void
     */
    public function setPathPagination()
    {
        $this->path = $this->options['uri'];
    }
}
