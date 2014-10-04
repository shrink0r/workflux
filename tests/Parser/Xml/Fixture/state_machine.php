<?php

return [
    'name' => 'video_transcoding',
    'states' => [
        'new' => [
            'name' => 'new',
            'type' => 'initial',
            'events' => [
                'promote' => [
                    'name' => 'promote',
                    'transitions' => [
                        [
                            'outgoing_state_name' => 'transcoding',
                            'guard' => [
                                'class' => 'Workflux\Guard\ExpressionGuard',
                                'options' => [
                                    'expression' => 'params.transcoding_required'
                                ]
                            ]
                        ],
                        [
                            'outgoing_state_name' => 'ready',
                            'guard' => [
                                'class' => 'Workflux\Guard\ExpressionGuard',
                                'options' => [
                                    'expression' => 'not params.transcoding_required'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'transcoding' => [
            'name' => 'transcoding',
            'type' => 'active',
            'events' => [
                'promote' => [
                    'name' => 'promote',
                    'transitions' => [
                        [
                            'outgoing_state_name' => 'ready',
                            'guard' => null
                        ]
                    ]
                ],
                'demote' => [
                    'name' => 'demote',
                    'transitions' => [
                        [
                            'outgoing_state_name' => 'error',
                            'guard' => [
                                'class' => 'Workflux\Guard\ExpressionGuard',
                                'options' => [
                                    'expression' => 'not params.retry_limit_reached'
                                ]
                            ]
                        ],
                        [
                            'outgoing_state_name' => 'rejected',
                            'guard' => [
                                'class' => 'Workflux\Guard\ExpressionGuard',
                                'options' => [
                                    'expression' => 'params.retry_limit_reached'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'error' => [
            'name' => 'error',
            'type' => 'active',
            'events' => [
                'promote' => [
                    'name' => 'promote',
                    'transitions' => [
                        [
                            'outgoing_state_name' => 'transcoding',
                            'guard' => null
                        ]
                    ]
                ],
                'demote' => [
                    'name' => 'demote',
                    'transitions' => [
                        [
                            'outgoing_state_name' => 'rejected',
                            'guard' => null
                        ]
                    ]
                ]
            ]
        ],
        'rejected' => [
            'name' => 'rejected',
            'type' => 'final',
            'events' => []
        ],
        'ready' => [
            'name' => 'ready',
            'type' => 'final',
            'events' => []
        ]
    ]
];
