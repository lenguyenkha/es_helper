<?php

namespace App\Libraries\ElasticSearch;

use Elasticsearch\Client;
use Illuminate\Support\Arr;

/**
 * Class ElasticSearch
 *
 */
class ElasticSearch
{
    /**
     * The default mapping type of index
     */
    const DEFAULT_MAPPING_TYPE = '_doc';

    /**
     * ElasticSearch Client
     *
     * @var Client $client ElasticSearch Client
     */
    private $client;

    /**
     * Name of index
     *
     * @var string $indexName the index name
     */
    private $indexName;

    /**
     * Pagination data
     *
     * @var array $pagination Pagination data
     */
    private $pagination = [];

    /**
     * The offset of pagination
     *
     * @var int $from Offset
     */
    private $from;

    /**
     * The limit or size of pagination
     *
     * @var int $size Size
     */
    private $size;

    /**
     * ElasticSearch constructor.
     *
     * @param Client $client ElasticSearch client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Set the index name to search
     *
     * @param string $name Name of index
     *
     * @return $this
     */
    public function setIndexName(string $name)
    {
        $this->indexName = $name;

        return $this;
    }

    /**
     * Get the current index name
     *
     * @return string
     */
    public function getIndexName()
    {
        return $this->indexName;
    }

    /**
     * Put the setting to current index
     *
     * @param array $settings Setting configure of current index
     *
     * @return $this
     */
    public function setSetting(array $settings)
    {
        $this->client->indices()->putSettings($settings);
        return $this;
    }

    /**
     * Get the current setting of index
     *
     * @param string $index Index name
     *
     * @return array
     */
    public function getSetting(string $index)
    {
        return $this->client->indices()->getSettings([
            'index' => $index
        ]);
    }

    /**
     * Put the mapping to current index
     *
     * @param array $mapping Mapping configure of current index
     *
     * @return $this
     */
    public function setMapping(array $mapping)
    {
        $this->client->indices()->putMapping($mapping);
        return $this;
    }

    /**
     * Get the current mapping of index
     *
     * @param string $index Index name
     *
     * @return array
     */
    public function getMapping(string $index)
    {
        return $this->client->indices()->getMapping([
            'index' => $index
        ]);
    }

    /**
     * Create a new index with setting and mapping configures
     *
     * @param string $indexName Index name
     * @param array $setting Setting configure
     * @param array $mapping Mapping configure
     *
     * @return array
     */
    public function createIndex(string $indexName, array $setting, array $mapping)
    {
        $params = [
            'index' => $indexName,
            'body' => [
                'settings' => $setting,
                'mappings' => $mapping
            ]
        ];
        return $this->client->indices()->create($params);
    }

    /**
     * Delete the existing index
     *
     * @param string $indexName Index name
     *
     * @return array
     */
    public function deleteIndex(string $indexName)
    {
        return $this->client->indices()->delete([
            'index' => $indexName
        ]);
    }

    /**
     * Indexing to elastic search with bulk data
     *
     * @param array $data Search data
     *
     * @return array
     */
    public function indexBulkData(array $data)
    {
        return $this->client->bulk($data);
    }

    /**
     * Building query searching by multi match condition
     *
     * @param string $query Query condition
     * @param string $analyzer Analyzer configuration
     * @param array $fields List of columns
     * @param string $type Match type
     *
     * @return array
     */
    public function buildMultiMatch(string $query, string $analyzer, array $fields, string $type = 'best_fields')
    {
        return [
            'query' => $query,
            'analyzer' => $analyzer,
            'fields' => $fields,
            'type' => $type
        ];
    }

    /**
     * Setting the pagination
     *
     * @param array $pagination Pagination data
     *
     * @return $this
     */
    public function setPagination(array $pagination)
    {
        $this->setFrom($pagination['from'])->setSize($pagination['size']);
        $this->pagination = $pagination;

        return $this;
    }

    /**
     * Setting the offset while searching
     *
     * @param int $from Offset
     *
     * @return $this
     */
    public function setFrom(int $from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * Getting the offset of searching
     *
     * @return int
     */
    public function from()
    {
        return $this->from;
    }

    /**
     * Setting the size of per page
     *
     * @param int $size Size
     *
     * @return $this
     */
    public function setSize(int $size)
    {
        $this->size = $size;
        return $this;
    }

    public function pagination()
    {
        return $this->pagination;
    }

    /**
     * Getting the size of per page
     *
     * @return int
     */
    public function size()
    {
        return $this->size;
    }

    /**
     * Creating a document to index
     *
     * @param string $indexName Index name
     * @param int $id Document Id
     * @param array $data Data index
     *
     * @return array
     */
    public function createDocument(string $indexName, int $id, array $data)
    {
        $doc = $this->getDocument($indexName, $id);
        if (empty($doc)) {
            $this->client->index([
                'index' => $indexName,
                'type' => self::DEFAULT_MAPPING_TYPE,
                'id' => (string)$id,
                'body' => $data
            ]);
        }
        return $this->getDocument($indexName, $id);
    }

    /**
     * Getting a document to index
     *
     * @param string $indexName Index name
     * @param int $id Document Id
     *
     * @return mixed
     */
    public function getDocument(string $indexName, int $id)
    {
        try {
            $result = $this->client->get([
                'index' => $indexName,
                'type' => self::DEFAULT_MAPPING_TYPE,
                'id' => (string)$id
            ]);
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $exception) {
            \Log::info($exception->getMessage());
            return null;
        }
        return $result;
    }

    /**
     * Updating a document to index
     *
     * @param string $indexName Index name
     * @param int $id Document Id
     * @param array $data Data index
     *
     * @return mixed
     */
    public function updateDocument(string $indexName, int $id, array $data)
    {
        $doc = $this->getDocument($indexName, $id);
        if (!empty($doc)) {
            $this->client->update([
                'index' => $indexName,
                'type' => self::DEFAULT_MAPPING_TYPE,
                'id' => (string)$id,
                'body' => [
                    'doc' => $data
                ]
            ]);
        }
        return $this->getDocument($indexName, $id);
    }

    /**
     * Deleting a document to index
     *
     * @param string $indexName Index name
     * @param int $id Document Id
     *
     * @return mixed
     */
    public function deleteDocument(string $indexName, int $id)
    {
        $doc = $this->getDocument($indexName, $id);
        if (!empty($doc)) {
            return $this->client->delete([
                'index' => $indexName,
                'type' => self::DEFAULT_MAPPING_TYPE,
                'id' => (string)$id
            ]);
        }
        return false;
    }

    /**
     * Search common function
     *
     * @param array $query The query for searching
     * @param bool $returnRaw Return raw of item
     * @param array $processScoreOptions Process score of es options
     *
     * @return mixed
     */
    public function search(array $query, bool $returnRaw = false, $processScoreOptions = [])
    {
        $body = array_merge($query, [
            'from' => $this->from(),
            'size' => $this->size()
        ]);

        $params = [
            'index' => $this->getIndexName(),
            'type' => self::DEFAULT_MAPPING_TYPE,
            'body' => $body
        ];

        $result = $this->searchOnElasticSearch($params);

        return $this->parseResponse($result, $returnRaw, $processScoreOptions);
    }

    /**
     * Search suggest function
     *
     * @param array $query The query for searching
     *
     * @return mixed
     */
    public function searchSuggest(array $query)
    {
        $params = [
            'index' => $this->getIndexName(),
            'type' => self::DEFAULT_MAPPING_TYPE,
            'body' => $query
        ];

        $result = $this->searchOnElasticSearch($params);

        return $result['suggest'];
    }

    /**
     * Analyze API
     * @param array $analyzer
     * @return array
     *
     * @author khaln <khaln@tech.est-rouge.com>
     */
    public function analyze(array $analyzer)
    {
        $params = [
            'index' => $this->getIndexName(),
            'body' => $analyzer
        ];

        return $this->client->indices()->analyze($params);
    }


    /**
     * Searching on elastic search
     *
     * @param array $params Parameters request
     *
     * @return array
     */
    private function searchOnElasticSearch(array $params)
    {
        return $this->client->search($params);
    }

    /**
     * Build the data from response of elastic search
     *
     * @param array $items Data items
     * @param bool $returnRaw Return raw of item
     * @param array $processScoreOptions Process score of es options
     *
     * @return array
     */
    public function parseResponse(array $items, bool $returnRaw = false, $processScoreOptions = [])
    {
        if (empty($items)) {
            $result = new ElasticSearchPaginator(
                collect([]),
                0,
                $this->pagination['size'],
                $this->pagination['page'],
                $this->pagination
            );

            return $result->toArray();
        }
        $total = $items['hits']['total']['value'];
        $records = $items['hits']['hits'];
        $rawResponse = !empty($processScoreOptions) ? $this->processScoreEs($records, $processScoreOptions) : $records;
        $responseData = [];
        foreach ($rawResponse as $item) {
            if (isset($item['highlight'])) {
                $item['_source']['highlight'] = $item['highlight'];
            }
            $responseData[] = $item;
        }

        $data = $returnRaw ? $responseData : Arr::pluck($responseData, '_source');

        $result = new ElasticSearchPaginator(
            collect($data),
            $total,
            $this->pagination['size'],
            $this->pagination['page'],
            $this->pagination
        );

        return $result->toArray();
    }

    public function processScoreEs($records, $options)
    {
        $result = [];
        $pointLayers = !empty($options['pointLayers']) ? $options['pointLayers'] : [];
        $points = array_flip($pointLayers);
        //Standard error of calculator when convert to int value
        $e = 0.001;
        foreach ($records as $record) {
            $resultRecord = $record;
            $rawScore = $record['_score'];
            if (!empty($points)) {
                $curRecordPoint = ($rawScore % 101 == 0) ? $rawScore / 101 : ($rawScore - $record['_source']['score'] + $e) / 101;
                $curRecordPoint = intval($curRecordPoint);
                $layerName = !empty($points[$curRecordPoint]) ? $points[$curRecordPoint] : $points[$curRecordPoint - 1];
                $resultRecord['_source']['layer_name'] = $layerName;
            }
            $resultRecord['_source']['raw_score_es'] = $record['_score'];
            $result[] = $resultRecord;
        }
        return $result;
    }
}
