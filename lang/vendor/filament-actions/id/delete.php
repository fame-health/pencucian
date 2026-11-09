<?php

return [
    'single' => [
        'label' => 'Hapus',
        'modal' => [
            'heading' => 'Hapus :label?',
            'actions' => [
                'delete' => [
                    'label' => 'Ya, hapus',
                ],
            ],
        ],
        'notifications' => [
            'deleted' => [
                'title' => 'Data berhasil dihapus',
            ],
        ],
    ],
    'multiple' => [
        'label' => 'Hapus data terpilih',
        'modal' => [
            'heading' => 'Hapus :label terpilih?',
            'actions' => [
                'delete' => [
                    'label' => 'Ya, hapus',
                ],
            ],
        ],
        'notifications' => [
            'deleted' => [
                'title' => 'Data terpilih berhasil dihapus',
            ],
            'deleted_partial' => [
                'title' => ':count dari :total data berhasil dihapus',
                'missing_authorization_failure_message' => 'Anda tidak memiliki izin untuk menghapus :count data.',
                'missing_processing_failure_message' => ':count data tidak dapat dihapus.',
            ],
            'deleted_none' => [
                'title' => 'Data gagal dihapus',
                'missing_authorization_failure_message' => 'Anda tidak memiliki izin untuk menghapus :count data.',
                'missing_processing_failure_message' => ':count data tidak dapat dihapus.',
            ],
        ],
    ],
];
