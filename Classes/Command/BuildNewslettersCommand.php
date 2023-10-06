<?php

namespace RKW\RkwNewsletter\Command;
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use RKW\RkwNewsletter\Domain\Repository\IssueRepository;
use RKW\RkwNewsletter\Mailing\MailProcessor;
use RKW\RkwNewsletter\Manager\IssueManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * class BuildNewslettersCommand
 *
 * Execute on CLI with: 'vendor/bin/typo3 rkw_newsletter:buildNewsletters'
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class BuildNewslettersCommand extends Command
{

    /**
     * issueRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository|null
     */
    protected ?IssueRepository $issueRepository = null;


    /**
     * MailProcessor
     *
     * @var \RKW\RkwNewsletter\Mailing\MailProcessor|null
     */
    protected ?MailProcessor $mailProcessor = null;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger|null
     */
    protected ?Logger $logger = null;


    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        $this->setDescription('Creates mails for all released newsletters.')
            ->addOption(
                'newsletterLimit',
                'l',
                InputOption::VALUE_REQUIRED,
                'Maximum number of newsletters to process on each call (default: 5)',
                5
            )
            ->addOption(
                'recipientsPerNewsletterLimit',
                'r',
                InputOption::VALUE_REQUIRED,
                'Maximum number of recipients per newsletters to process on each call (default: 50)',
                50
            )
            ->addOption(
                'sleep',
                's',
                InputOption::VALUE_REQUIRED,
                'How many seconds the script should sleep after each run (default: 10)',
                10
            );
    }


    /**
     * Initializes the command after the input has been bound and before the input
     * is validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @see \Symfony\Component\Console\Input\InputInterface::bind()
     * @see \Symfony\Component\Console\Input\InputInterface::validate()
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->issueRepository = $objectManager->get(IssueRepository::class);
        $this->mailProcessor = $objectManager->get(MailProcessor::class);
    }


    /**
     * Executes the command for showing sys_log entries
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @see \Symfony\Component\Console\Input\InputInterface::bind()
     * @see \Symfony\Component\Console\Input\InputInterface::validate()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $newsletterLimit = $input->getOption('newsletterLimit');
        $recipientsPerNewsletterLimit = $input->getOption('recipientsPerNewsletterLimit');
        $sleep = $input->getOption('sleep');

        $result = 0;
        try {

            $issues = $this->issueRepository->findAllToSend($newsletterLimit);
            if (count($issues)) {

                /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
                foreach ($issues as $issue) {

                    $this->mailProcessor->setIssue($issue);
                    $this->mailProcessor->setRecipients();
                    $this->mailProcessor->sendMails($recipientsPerNewsletterLimit);

                    usleep(intval($sleep * 1000000));
                }

            } else {
                $message = 'No issues to build.';
                $io->note($message);
                $this->getLogger()->log(LogLevel::INFO, $message);
            }

        } catch (\Exception $e) {

            $message = sprintf('An unexpected error occurred while trying to update the statistics of e-mails: %s',
                str_replace(array("\n", "\r"), '', $e->getMessage())
            );

            // @extensionScannerIgnoreLine
            $io->error($message);
            $this->getLogger()->log(LogLevel::ERROR, $message);
            $result = 1;
        }

        $io->writeln('Done');
        return $result;

    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): Logger
    {
        if (!$this->logger instanceof Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }
}
