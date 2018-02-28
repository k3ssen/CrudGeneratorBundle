<?php
declare(strict_types=1);

namespace K3ssen\CrudGeneratorBundle\Command;

use K3ssen\CrudGeneratorBundle\Generator\CrudGenerator;
use K3ssen\MetaEntityBundle\MetaData\MetaEntityFactory;
use K3ssen\MetaEntityBundle\Reader\ExistingEntityToMetaEntityReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CrudCommand extends Command
{
    protected static $defaultName = 'crud:create';

    /** @var ExistingEntityToMetaEntityReader */
    protected $existingEntityToMetaEntityReader;

    /** @var MetaEntityFactory */
    protected $metaEntityFactory;

    /** @var CrudGenerator */
    protected $crudGenerator;

    public function __construct(
        ?string $name = null,
        CrudGenerator $crudGenerator,
        MetaEntityFactory $metaEntityFactory,
        ExistingEntityToMetaEntityReader $existingEntityToMetaEntityReader
    ) {
        parent::__construct($name);
        $this->crudGenerator = $crudGenerator;
        $this->metaEntityFactory = $metaEntityFactory;
        $this->existingEntityToMetaEntityReader = $existingEntityToMetaEntityReader;
    }

    protected function configure()
    {
        $this->setDescription('Generate crud for existing entity')
            ->addArgument('entity', InputArgument::OPTIONAL, 'Argument description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Generate CRUD');
        $choices = $this->metaEntityFactory->getEntityOptions();
        if (count($choices) === 0) {
            $io->error('No entities found; Add some entities first.');
            return;
        }
        $choice = $io->choice('Entity', $choices, $input->getArgument('entity'));
        $metaEntity = $this->metaEntityFactory->getMetaEntityByChosenOption($choice);

        $this->existingEntityToMetaEntityReader->extractExistingClassToMetaEntity($metaEntity);

        $files = $this->crudGenerator->createCrud($metaEntity);
        foreach ($files as $file) {
            $io->success(sprintf('Created/Updated file %s', $file));
        }
    }
}
