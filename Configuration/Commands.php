<?php

return [
    'rkw_newsletter:createIssues' => [
        'class' => \RKW\RkwNewsletter\Command\CreateIssuesCommand::class,
        'schedulable' => true,
    ],
    'rkw_newsletter:processConfirmations' => [
        'class' => \RKW\RkwNewsletter\Command\ProcessConfirmationsCommand::class,
        'schedulable' => true,
    ],
    'rkw_newsletter:buildNewsletters' => [
        'class' => \RKW\RkwNewsletter\Command\BuildNewslettersCommand::class,
        'schedulable' => true,
    ],
];
