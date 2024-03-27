<?php
declare(strict_types = 1);

return [
    \RKW\RkwNewsletter\Domain\Model\BackendUser::class => [
        'tableName' => 'be_users',
    ],
    \RKW\RkwNewsletter\Domain\Model\FrontendUser::class => [
        'tableName' => 'fe_users',
    ],
    \RKW\RkwNewsletter\Domain\Model\Content::class => [
        'tableName' => 'tt_content',
        'properties' => [
            'uid' => [
                'fieldName' => 'uid'
            ],
            'pid' => [
                'fieldName' => 'pid'
            ],
            'contentType' => [
                'fieldName' => 'CType'
            ],
            'imageCols' => [
                'fieldName' => 'imagecols'
            ],
            'sysLanguageUid' => [
                'fieldName' => 'sys_language_uid'
            ],
        ],
    ],
    \RKW\RkwNewsletter\Domain\Model\Pages::class => [
        'tableName' => 'pages',
        'properties' => [
            'permsUserId' => [
                'fieldName' => 'perms_userid'
            ],
            'permsGroupId' => [
                'fieldName' => 'perms_groupid'
            ],
            'permsUser' => [
                'fieldName' => 'perms_user'
            ],
            'permsGroup' => [
                'fieldName' => 'perms_group'
            ],
            'permsEverybody' => [
                'fieldName' => 'perms_everybody'
            ],
        ],
    ],
];
