<?php


namespace App\Libraries\ElasticSearch;

/**
 * Class ElasticSearchQueryBuilder
 *
 */
class ElasticSearchQueryBuilder
{
    function __queryExact($field, $value)
    {
        return [
            "constant_score" => [
                "filter" => [
                    "term" => [
                        $field => $value
                    ]
                ]
            ]

        ];
    }

    function __should($arr, $boost = false, $options = [])
    {
        if (count($arr) > 1) {
            $query = [
                "bool" => array_merge([
                    "should" => $arr
                ], $options),
            ];

            if ($boost) {
                $query['bool']['boost'] = $boost;
            }

            return $query;
        } else {
            return $arr[0];
        }
    }

    function __shouldV2($arr, $options = [])
    {
        if ((count($arr) > 1 && isset($arr[0])) || !empty($options)) {
            $query = [
                "bool" => array_merge([
                    "should" => $arr
                ], $options),
            ];

            return $query;
        }

        if (isset($arr[0])) {
            return $arr[0];
        }

        return $arr;
    }

    function __must_not($arr, $boost = false, $options = [])
    {
        $query = [
            "bool" => array_merge([
                "must_not" => $arr
            ], $options)
        ];

        if ($boost) {
            $query['bool']['boost'] = $boost;
        }

        return $query;

    }

    function __must($arr, $boost = false, $options = [])
    {

        $query = [
            "bool" => array_merge([
                "must" => $arr
            ], $options)
        ];

        if ($boost) {
            $query['bool']['boost'] = $boost;
        }

        return $query;
    }

    function __highlight($fields, $options = []) {
        if (empty($fields)) {
            return [];
        }

        $baseHighlight = $options;

        $baseHighlight['fields'] = [];
        foreach ($fields as $field) {
            if (empty($field)) {
                continue;
            }
            if (is_array($field)) {
                $baseHighlight['fields'] = array_merge($baseHighlight['fields'], $field);
                continue;
            }
            $baseHighlight['fields'][$field] = ["type" => "plain"];
        }

        return !empty($baseHighlight['fields']) ? $baseHighlight : [];
    }

    function __term($values, $fields, $boost = false)
    {
        if (count($fields) == 1) {
            $field = $fields[0];

            if ($boost) {
                if (count($values) > 1) {
                    $query = [
                        "constant_score" => [
                            "filter" => [
                                'terms' => [
                                    $field => $values
                                ]
                            ],
                            "boost" => $boost
                        ]
                    ];
                } else {
                    $query = [
                        "constant_score" => [
                            "filter" => [
                                'term' => [
                                    $field => $values[0]
                                ]
                            ],
                            "boost" => $boost
                        ]
                    ];
                }
            } else {
                if (count($values) > 1) {
                    $query = [
                        'terms' => [
                            $field => $values
                        ]
                    ];
                } else {
                    $query = [
                        'term' => [
                            $field => $values[0]
                        ]
                    ];
                }
            }

        } else {
            $arrQueries = [];
            foreach ($fields as $field) {
                $arrQueries[] = $this->__term($values, [$field]);
            }

            $query = $this->__should($arrQueries, $boost);
        }

        return $query;
    }

    function __multiMatch($matchType, $values, $fields, $boost = false, $wrapShould = true, $options = [])
    {
        $matchType = strtolower($matchType);

        $queries = [];

        if (empty($values)) {
            return $queries;
        }

        foreach ($values as $value) {
            $query = [
                "multi_match" => [
                    "query" => $value,
                    "fields" => $fields
                ]
            ];

            if (in_array($matchType, ['phrase', 'phrase_prefix'])) {
                $query['multi_match']['type'] = $matchType;
            }

            if ($boost) {
                $query['multi_match']['boost'] = $boost;
            }

            $query['multi_match'] = array_merge($query['multi_match'], $options);

            $queries[] = $query;
        }

        return (count($queries) < 2) ? $queries[0] : (($wrapShould) ? $this->__should($queries, $boost) : $queries);
    }

    function __multiMatchV2($matchType, $values, $fields, $options = [])
    {
        $boost = !empty($options['boost']) ? $options['boost'] : false;
        $wrapShould = !empty($options['wrapShould']) ? $options['wrapShould'] : true;

        $matchType = strtolower($matchType);

        $queries = [];

        if (empty($values)) {
            return $queries;
        }

        foreach ($values as $value) {
            $query = [
                "multi_match" => [
                    "query" => $value,
                    "fields" => $fields
                ]
            ];

            if (in_array($matchType, ['phrase', 'phrase_prefix'])) {
                $query['multi_match']['type'] = $matchType;
            }

            if ($boost) {
                $query['multi_match']['boost'] = $boost;
            }

            $query['multi_match'] = array_merge($query['multi_match'], $options);

            $queries[] = $query;
        }

        return (count($queries) < 2) ? $queries[0] : (($wrapShould) ? $this->__should($queries, $boost) : $queries);
    }

    function __functionScore($query, $script, $mode = [])
    {
        if (!empty($script)) {
            $base = [
                    "function_score" => array_merge([
                        "query" => $query,
                        "script_score" => [],
                    ], $mode)
            ];

            if (is_array($script)) {
                $base['function_score']['script_score']['script'] = $script;
                return $base;
            }

            $base['function_score']['script_score']['script']['source'] = $script;
            return $base;
        }

        return [
            "function_score" => array_merge([
                "query" => $query,
            ], $mode)
        ];

    }

    function __suggestChildObject($suggestName, $typeSuggest, $fieldName, $options = [])
    {
        return [
            $suggestName => [
                $typeSuggest => array_merge(['field' => $fieldName], $options)
            ]
        ];
    }

    function __suggest($text, $arrSuggest)
    {
        $suggest = ['text' => $text];
        foreach ($arrSuggest as $suggestChildNode) {
            $options = (isset($suggestChildNode['options'])) ? $suggestChildNode['options'] : [];
            $suggest = array_merge($suggest, $this->__suggestChildObject(
                $suggestChildNode['suggestName'],
                $suggestChildNode['typeSuggest'],
                $suggestChildNode['fieldName'],
                $options
            ));
        }
        return ['suggest' => $suggest];
    }

    function __prefix($field, $value, $options = [])
    {
        $query = [
            'prefix' => [
                $field => [
                    'value' => $value
                ]
            ]

        ];

        if (!empty($options)) {
            $query['prefix'][$field] = array_merge($query['prefix'][$field], $options);

        }

        return $query;
    }

    function __matchPhrase($field, $value, $options = [])
    {
        $query = [
            'match_phrase' => [
                $field => [
                    'query' => $value
                ]
            ]

        ];

        if (!empty($options)) {
            $query['match_phrase'][$field] = array_merge($query['match_phrase'][$field], $options);
        }
        return $query;
    }

    function __matchPhrasePrefix($field, $value, $options = [])
    {
        $query = [
            'match_phrase_prefix' => [
                $field => [
                    'query' => $value
                ]
            ]

        ];

        if (!empty($options)) {
            $query['match_phrase_prefix'][$field] = array_merge($query['match_phrase_prefix'][$field], $options);
        }
        return $query;
    }

    function __match($field, $value, $optionals = [])
    {
        $query = [
            'match' => [
                $field => [
                    'query' => $value
                ]
            ]
        ];

        if (!empty($optionals)) {
            $query['match'][$field] = array_merge($query['match'][$field], $optionals);
        }
        return $query;
    }

    function __nested($path, $queryType, $values, $fields = [], $boost = false)
    {
        switch ($queryType) {
            case 'term':
                $arrQueries = [];
                foreach ($fields as $field) {
                    $nestedQuery = [
                        "nested" => [
                            "path" => $path,
                            "query" => []
                        ]
                    ];

                    if (!empty($boost)) {
                        $nestedQuery['nested']['boost'] = $boost;
                    }

                    $nestedQuery['nested']['query'] = $this->__term($values, [$field]);

                    $arrQueries[] = $nestedQuery;
                }

                return $this->__should($arrQueries, $boost);
                break;
            case 'multi_match_phrase':
                $nestedQuery = [
                    "nested" => [
                        "path" => $path,
                        "query" => []
                    ]
                ];

                if (!empty($boost)) {
                    $nestedQuery['nested']['boost'] = $boost;
                }

                $nestedQuery['nested']['query'] = $this->__multiMatch('phrase', $values, $fields);

                return $nestedQuery;
                break;
            case 'multi_match':
                $nestedQuery = [
                    "nested" => [
                        "path" => $path,
                        "query" => []
                    ]
                ];

                if (!empty($boost)) {
                    $nestedQuery['nested']['boost'] = $boost;
                }

                $nestedQuery['nested']['query'] = $this->__multiMatch('', $values, $fields);

                return $nestedQuery;
                break;
            case 'range':
                $arrQueries = [];
                foreach ($fields as $field) {
                    $nestedQuery = [
                        "nested" => [
                            "path" => $path,
                            "query" => []
                        ]
                    ];

                    if (!empty($boost)) {
                        $nestedQuery['nested']['boost'] = $boost;
                    }

                    $nestedQuery['nested']['query'] = $this->__range($field, $values, $boost);

                    $arrQueries[] = $nestedQuery;
                }

                return $arrQueries;

                break;
            default:
                break;
        }
    }

    function __range($field, $conditions, $boost = false)
    {
        $query = [
            "range" => [
                $field => []
            ]
        ];

        foreach ($conditions as $key => $value) {
            $query['range'][$field][$key] = $value;
        }

        return $query;
    }


}