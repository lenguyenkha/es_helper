<?php
return [
    "validation" => [
        "search_text" => [
            "text.string" => [
                "title" => "The text must be a string.",
                "detail" => "The text must be a string.",
                "code" => "ESE-422101"
            ],
            "return_raw.boolean" => [
                "title" => "The return_raw field must be 0 or 1.",
                "detail" => "The return_raw field must be 0 or 1.",
                "code" => "ESE-422102"
            ],
            "order_by_score.boolean" => [
                "title" => "The order_by_score field must be 0 or 1.",
                "detail" => "The order_by_score field must be 0 or 1.",
                "code" => "ESE-422103"
            ],
            "debug.boolean" => [
                "title" => "The debug field must be 0 or 1.",
                "detail" => "The debug field must be 0 or 1.",
                "code" => "ESE-422104"
            ],
            "search_sub_word.boolean" => [
                "title" => "The search_sub_word field must be 0 or 1.",
                "detail" => "The search_sub_word field must be 0 or 1.",
                "code" => "ESE-422105"
            ],
            "suggest_length_rule.boolean" => [
                "title" => "The suggest_length_rule field must be 0 or 1.",
                "detail" => "The suggest_length_rule field must be 0 or 1.",
                "code" => "ESE-422106"
            ],
            "search_other_names_with_1st_letter.boolean" => [
                "title" => "The search_other_names_with_1st_letter field must be 0 or 1.",
                "detail" => "The search_other_names_with_1st_letter field must be 0 or 1.",
                "code" => "ESE-422107"
            ],
            "page.numeric" => [
                "title" => "The page must be a number.",
                "detail" => "The page must be a number.",
                "code" => "ESE-422108"
            ],
            "page.min" => [
                "numeric" => [
                    "title" => "The page must be at least 1.",
                    "detail" => "The page must be at least 1.",
                    "code" => "ESE-422109"
                ],
            ],
            "limit.numeric" => [
                "title" => "The limit must be a number.",
                "detail" => "The limit must be a number.",
                "code" => "ESE-422110"
            ],
            "limit.min" => [
                "numeric" => [
                    "title" => "The limit must be at least 1.",
                    "detail" => "The limit must be at least 1.",
                    "code" => "ESE-422111"
                ]
            ],

        ],
        "analyze_text" => [
            "text.required" => [
                "title" => "The text must be required.",
                "detail" => "The text must be required.",
                "code" => "ESE-422201"
            ],
            "text.string" => [
                "title" => "The text must be a string.",
                "detail" => "The text must be a string.",
                "code" => "ESE-422202"
            ],
            "analyzers.required" => [
                "title" => "The analyzers must be required.",
                "detail" => "The analyzers must be required.",
                "code" => "ESE-422203"
            ],
            "analyzers.string" => [
                "title" => "The analyzers must be a array with format comma.",
                "detail" => "The analyzers must be a array with format comma.",
                "code" => "ESE-422204"
            ],

        ]
    ]
];