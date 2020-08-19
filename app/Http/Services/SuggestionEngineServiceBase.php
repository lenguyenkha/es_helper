<?php


namespace App\Http\Services;

use App\Exceptions\AppBaseException;
use App\Libraries\ElasticSearch\ElasticSearch;
use App\Libraries\ElasticSearch\ElasticSearchQueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

/**
 * Class SuggestionEngineServiceBase
 * @package App\Services
 *
 */
class SuggestionEngineServiceBase
{
    /**
     * The number limit of items will be gotten from result of search
     */
    protected $searchLimit = 10;

    /**
     * The number limit check ngram
     */
    protected $lengthCheckNgram = 8;

    /**
     * The number limit need to gt for search Related Info 65w
     */
    protected $lengthSearchRelatedInfo65w = 1;

    /**
     * The number limit need to gt for search Related Info 100kw
     */
    protected $lengthSearchRelatedInfo100kw = 2;

    /**
     * @var $elasticSearch ElasticSearch
     *
     */
    protected $elasticSearch;

    /**
     * @var $elasticSearchQueryBuilder ElasticSearchQueryBuilder
     *
     */
    protected $elasticSearchQueryBuilder;

    const AVAILABLE_OTHER_SOURCES = [
        'advance_search_data',
        'search_console_data',
        'google_analytic_data',
        'search_console_linked_data',
        'google_trend_related_data',
        'google_trend_raising_data',
        'google_keyword_planner_data',
        'categories'
    ];

    /**
     * @var $debug array
     *
     */
    protected $debug = [];

    /**
     * @var $layer
     *
     */
    protected $layer = [];

    /**
     * @var $point
     *
     */
    protected $point = [];

    public function __construct(ElasticSearch $elasticSearch, ElasticSearchQueryBuilder $elasticSearchQueryBuilder)
    {
        $this->elasticSearch = $elasticSearch;
        $this->elasticSearchQueryBuilder = $elasticSearchQueryBuilder;
        $this->searchLimit = config('ese_constants.search_limit');
        $this->lengthCheckNgram = config('ese_constants.length_check_ngram');
        $this->lengthSearchRelatedInfo65w = config('ese_constants.length_search_related_info_65w');
        $this->lengthSearchRelatedInfo100kw = config('ese_constants.length_search_related_info_100kw');
    }

    public function setPagination(Request $request)
    {
        $pagination = [
            'page' => isset($request->page) ? (int)$request->page : 1,
            'size' => isset($request->limit) ? (int)$request->limit : $this->searchLimit,
            'uri' => $request->url(),
            'query' => $request->query()
        ];
        $pagination['from'] = ($pagination['page'] - 1) * $pagination['size'];
        $this->elasticSearch->setPagination($pagination);
    }

    /**
     * Get point priority of layers
     *
     * @param $options
     * @return array
     */
    protected function getPointLayers($options)
    {
        return [];
    }

    /**
     * Get filter remove no need document
     * @param $options
     *
     * @return array
     */
    protected function getFilterLogic($options = [])
    {
        return [
            "must_not" => [
                ["term" => ["template_type" => "00"]],
                $this->elasticSearchQueryBuilder->__must([
                    ["term" => ["template_type" => "99"]],
                    $this->elasticSearchQueryBuilder->__must_not(["exists" => ["field" => "url"]])
                ])
            ]
        ];
    }

    /**
     * Get filterWordId
     *
     * @param $dataQuery
     * @param $wordId
     * @return array
     *
     */
    protected function getFilterWordId($dataQuery, $wordId)
    {
        return $this->elasticSearchQueryBuilder->__must([
            $this->elasticSearchQueryBuilder->__functionScore(["term" => ["word_id" => $wordId]], "0.0", ['boost_mode' => 'replace']),
            $dataQuery
        ]);
    }

    /**
     * Get highlight for analyze text
     *
     * @return array
     */
    protected function getHighlightForAnalyzeText()
    {

        return $this->elasticSearchQueryBuilder->__highlight(
            [
                [
                    'name' => [
                        'type' => 'plain',
                        'pre_tags' => ["<mark class='name'>"],
                        'post_tags' => ["</mark>"]
                    ]
                ],
                [
                    'name.standard' => [
                        'type' => 'plain',
                        'pre_tags' => ["<mark class='name-standard'>"],
                        'post_tags' => ["</mark>"]
                    ]
                ],
                [
                    'name.sudachi' => [
                        'type' => 'plain',
                        'pre_tags' => ["<mark class='name-sudachi'>"],
                        'post_tags' => ["</mark>"]
                    ]
                ],
                [
                    'name.sudachi_shingle' => [
                        'type' => 'plain',
                        'pre_tags' => ["<mark class='name-sudachi-shingle'>"],
                        'post_tags' => ["</mark>"]
                    ]
                ],
                [
                    'name.edge_ngram_2_10' => [
                        'type' => 'plain',
                        'pre_tags' => ["<mark class='name-edge_ngram_2_10'>"],
                        'post_tags' => ["</mark>"]
                    ]
                ],
                [
                    'name.ngram_2_3' => [
                        'type' => 'plain',
                        'pre_tags' => ["<mark class='name-ngram_2_3'>"],
                        'post_tags' => ["</mark>"]
                    ]
                ],
                [
                    'other_names' => [
                        'type' => 'plain',
                        'pre_tags' => ["<mark class='other_names'>"],
                        'post_tags' => ["</makr>"]
                    ]
                ],
                [
                    'other_names.standard' => [
                        'type' => 'plain',
                        'pre_tags' => ["<mark class='other_names-standard'>"],
                        'post_tags' => ["</mark>"]
                    ]
                ],
                [
                    'other_names.sudachi_shingle' => [
                        'type' => 'plain',
                        'pre_tags' => ["<mark class='other_names-sudachi-shingle'>"],
                        'post_tags' => ["</mark>"]
                    ]
                ],
                [
                    'other_names.edge_ngram_2_10' => [
                        'type' => 'plain',
                        'pre_tags' => ["<mark class='other_names-edge_ngram_2_10'>"],
                        'post_tags' => ["</mark>"]
                    ]
                ],
                [
                    'other_names.ngram_2_3' => [
                        'type' => 'plain',
                        'pre_tags' => ["<mark class='other_names-ngram_2_3'>"],
                        'post_tags' => ["</mark>"]
                    ]
                ],
            ],
            [
                "tags_schema" => "styled",
                "pre_tags" => ["<mark>"],
                "post_tags" => ["</mark>"]
            ]
        );

    }

    /**
     * Get highlight for analyze text
     *
     * @return array
     */
    protected function getHighlightForList()
    {

        return $this->elasticSearchQueryBuilder->__highlight(
            [
                '*'
            ],
            [
                "tags_schema" => "styled",
                "pre_tags" => ["<mark>"],
                "post_tags" => ["</mark>"]
            ]
        );

    }

    /**
     * Add layer
     *
     * @param array $input
     *  - int point: Point of logic
     *  - array options: Options for build score
     * @param array $options
     * @return array
     * @throws AppBaseException
     *
     */
    protected function addLayer(array $input, $options = [])
    {
        $layerName = !empty($options['layerName']) ? $options['layerName'] : null;
        $score = !empty($options['score']) ? $options['score'] : [];
        $filter = !empty($options['filter']) ? $options['filter'] : [];
        if (!empty($layerName)) {
            if (!is_string($layerName)) {
                throw new AppBaseException(null, "Layer name must is a string");
            }

            if (array_key_exists($layerName, $this->layer)) {
                throw new AppBaseException(null, "Layer name can't empty");
            }
        }
        $layer = [];
        if (empty($input)) {
            if (!empty($layerName)) {
                $index = count($this->layer);
                $this->layer[$layerName] = [
                    'index' => $index,
                    'data' => []
                ];
            }

            return [];
        }
        if (!isset($input[0])) {
            $layer = $input;
        } else {
            foreach ($input as $item) {
                if (empty($item)) {
                    continue;
                }
                if (count($item) > 1 && isset($item[0])) {
                    $layer = array_merge($layer, $item);
                } else {
                    $layer[] = $item;
                }
            }
        }

        if (empty($layer)) {
            if (!empty($layerName)) {
                $index = count($this->layer);
                $this->layer[$layerName] = [
                    'index' => $index,
                    'data' => []
                ];
            }
            return [];
        }

        $query = $this->elasticSearchQueryBuilder->__shouldV2($layer, $filter);

        if (!empty($score) || (!empty($layerName) && isset($this->point[$layerName]))) {
            $pointLayer = (isset($this->point[$layerName])) ? $this->point[$layerName] : $score['point'];
            $optionsScorePoint = !empty($score['options']) ? $score['options'] : [];
            $scoreLayer = $this->buildScoreLayer($pointLayer, $optionsScorePoint);
            $query = $this->elasticSearchQueryBuilder->__functionScore($query, (string)$scoreLayer, ["boost_mode" => "replace"]);
        }

        if (!empty($layerName)) {
            $index = count($this->layer);
            $this->layer[$layerName] = [
                'index' => $index,
                'data' => $query
            ];
        }
        return $query;
    }

    /**
     * Accept options
     * - matchType: phrase|phrasePrefix|prefix
     * - matchOption
     * @param string $text
     * @param string|array $field
     * @param boolean $condition
     * @param array $score : score point of logic
     *  - int point: Point of logic
     *  - array options: Options for build score
     * @param array $options
     * @return array
     */
    protected function addMatchLogic($text, $field, $condition = true, $score = [], $options = [])
    {
        if (!$condition) {
            return [];
        }
        $matchType = !empty($options['matchType']) ? $options['matchType'] : 'match';
        $matchOption = !empty($options['matchOption']) ? $options['matchOption'] : [];

        switch ($matchType) {
            case 'phrase':
                $query = $this->elasticSearchQueryBuilder->__matchPhrase($field, $text, $matchOption);
                break;
            case 'phrasePrefix':
                $query = $this->elasticSearchQueryBuilder->__matchPhrasePrefix($field, $text, $matchOption);
                break;
            case 'prefix':
                $query = $this->elasticSearchQueryBuilder->__prefix($field, $text, $matchOption);
                break;
            default:
                $query = $this->elasticSearchQueryBuilder->__match($field, $text, $matchOption);

        }
        if (!empty($score)) {
            $pointLayer = $score['point'];
            $optionsScorePoint = !empty($score['options']) ? $score['options'] : [];
            $scoreLayer = $this->buildScoreLayer($pointLayer, $optionsScorePoint);
            $query = $this->elasticSearchQueryBuilder->__functionScore($query, (string)$scoreLayer, ["boost_mode" => "replace"]);
        }

        return $query;
    }

    /**
     * Get reading form of text
     *  Accept options:
     *  - analyzer
     *  - tokenizer
     *  - char_filter
     *  - filter
     * @param $text
     * @param null $index
     * @param array $options
     * @return string
     */
    public function getReadingForm($text, $index = null, $options = [])
    {
        $response = $this->analyzeTextToTokens($text, $index, $options);
        $readingForm = '';
        if (isset($response) && isset($response['tokens']) && !empty($response['tokens'])) {
            foreach ($response['tokens'] as $index => $token) {
                $readingForm .= $token['token'];
            }
        }
        return $readingForm;
    }

    /**
     * AnalyzeTextToTokens
     *  Accept options:
     *  - analyzer
     *  - tokenizer
     *  - char_filter
     *  - filter
     * @param $text
     * @param null $index
     * @param array $options
     * @return mixed
     */
    public function analyzeTextToTokens($text, $index = null, $options = [])
    {
        if (!empty($index)) {
            $this->elasticSearch->setIndexName($index);
        }

        $optionsAnalyze = [];
        if (!empty($options['analyzer'])) {
            $optionsAnalyze['analyzer'] = $options['analyzer'];
        } else {
            $optionsDefault = [
                'tokenizer' => 'sudachi_tokenizer',
                'char_filter' => [
                    'hiragana_to_katakana',
                    'char_filter_ja',
                ],
                'filter' => [
                    'trim',
                    'cjk_width',
                    'lowercase',
                    'sudachi_katakana_readingform'
                ],
            ];
            $optionsAnalyze = array_merge($optionsDefault, $options);
        }

        $query = array_merge($optionsAnalyze, ['text' => $text]);

        return $this->elasticSearch->analyze($query);

    }

    /**
     * Get options data of suggester
     * @param $suggestName
     * @param $suggestData
     * @return array
     *
     */
    protected function getOptionsSuggestData($suggestName, $suggestData)
    {
        $dataOptions = array_map(function ($suggestItem) {
            return $suggestItem['options'];
        }, $suggestData[$suggestName]);

        $dataFinal = [];
        foreach ($dataOptions as $optionItem) {
            if (!empty($optionItem) && sizeof($optionItem) > 0) {
                $dataFinal = array_merge($dataFinal, $optionItem);
            }
        }

        return $dataFinal;
    }

    /**
     * Build score of layer input
     * Accept option:
     *  - weight: weight of score
     *  - boostExp: boost Exp
     *  - base: base of Score
     * @param $pointLayer
     * @param array $options
     * @return float|int
     *
     */
    protected function buildScoreLayer($pointLayer, $options = [])
    {
        $weight = empty($options['weight']) ? 1 : (int)$options['weight'];
        $weight = ($weight != 0) ? $weight : 1;
        $boostExp = empty($options['boostExp']) ? -3 : (int)$options['boostExp'];
        $base = empty($options['base']) ? 2 : (int)$options['base'];
        $base = ($base < 2) ? 2 : $base;
        return $weight * pow($base, $boostExp) * pow($base, $pointLayer);
    }

    /**
     * Build final script wrap all layers
     * Accept option:
     *  - weight: weight of score
     *  - boostExp: boost Exp
     *  - base: base of Score
     *  - orderByScore: order by score
     * @param array $options
     * @return array
     *
     */
    protected function buildScriptScoreForLayers($options = [])
    {
        $base = empty($options['base']) ? 2 : (int)$options['base'];
        $base = ($base < 2) ? 2 : $base;
        $boostExp = empty($options['boostExp']) ? 3 : -(int)$options['boostExp'];
        $weight = empty($options['weight']) ? 1 : (int)$options['weight'];
        $weight = ($weight != 0) ? $weight : 1;
        if (!empty($options['orderByScore']) && $options['orderByScore']) {
            return [
                'params' => [
                    'base' => $base,
                    'boostExp' => $boostExp,
                    'weight' => $weight
                ],
                'source' => "Math.floor(params.boostExp+(Math.log(_score)-Math.log(params.weight))/Math.log(params.base))*101+doc['score'].value"
            ];
        }
        return [
            'params' => [
                'base' => $base,
                'boostExp' => $boostExp,
                'weight' => $weight
            ],
            'source' => "Math.floor(params.boostExp+(Math.log(_score)-Math.log(params.weight))/Math.log(params.base))*101"
        ];
    }
}