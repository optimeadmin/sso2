<?php

declare(strict_types=1);

namespace Optime\Sso\Bundle\Client\Command;

use Optime\Sso\Bundle\Client\Token\LocalTokenGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'optime:sso:login', description: 'Genera un link para hacer login sso localmente')]
class LocalLoginCommand extends Command
{
    public function __construct(
        private readonly LocalTokenGenerator $tokenGenerator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('ttl', null, InputOption::VALUE_OPTIONAL, 'Tiempo de expiración del token en segundos', 60);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $ttl = (int) $input->getOption('ttl');
        $jwt = $this->tokenGenerator->generate($ttl);

        $io->warning(sprintf('Recuerda que el token tiene una vigencia de %d segundos', $ttl));
        $io->title('Copia y pega este token al final de la url donde desees hacer login sso');
        $io->section(sprintf('?sso-local-token=%s', $jwt));

        return Command::SUCCESS;
    }
}