<?php

declare(strict_types=1);

namespace Dayploy\DoctrineExtensionsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateDoctrineEncryptionKeyCommand extends Command
{
    public function __construct(
    ) {
        parent::__construct('dayploy:doctrine-extensions:generate');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $io = new SymfonyStyle($input, $output);

        $key = '0x'.bin2hex(sodium_crypto_secretbox_keygen());
        $io->writeln('Generated Key to put in .env: DOCTRINE_ENCRYPTION_KEY='.$key);

        return Command::SUCCESS;
    }
}
