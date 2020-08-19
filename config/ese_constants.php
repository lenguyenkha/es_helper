<?php

return [
    'length_check_ngram' => (int)env('LENGTH_CHECK_NGRAM', 8),
    'search_limit' => (int)env('SEARCH_LIMIT', 100),
    'length_search_related_info_65w' => (int)env('LENGTH_SEARCH_RELATED_INFO_65W', 1),
    'length_search_related_info_100kw' => (int)env('LENGTH_SEARCH_RELATED_INFO_100KW', 2),
];