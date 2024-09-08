<?php

namespace mar4ehk0\OCRBundle\Yandex\Command;

use mar4ehk0\OCRBundle\Yandex\UseCase\CreateIAMToken\CreatorIAMToken;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(name: 'ocr:yandex:create_iam_token')]
class CreateIAMTokenCommand extends Command
{
    public function __construct(private readonly CreatorIAMToken $creatorIAMToken)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Creates IAM Token for Yandex.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->creatorIAMToken->createIAMToken();

            return Command::SUCCESS;
        } catch (Throwable $e) {
            var_dump($e->getMessage());

            return Command::FAILURE;
        }
    }
}
