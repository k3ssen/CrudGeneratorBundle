<?php
declare(strict_types=1);

namespace K3ssen\CrudGeneratorBundle\Generator;

use Doctrine\Common\Util\Inflector;
use K3ssen\EntityGeneratorBundle\Generator\GeneratorFileLocatorTrait;
use K3ssen\MetaEntityBundle\MetaData\MetaEntityInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Config\FileLocator;

class CrudGenerator
{
    use GeneratorFileLocatorTrait;

    /** @var \Twig_Environment */
    protected $twig;

    protected $projectDir;

    public function __construct(FileLocator $fileLocator, \Twig_Environment $twig, string $projectDir) {
        $this->fileLocator = $fileLocator;
        $this->twig = $twig;
        $this->projectDir = $projectDir;
    }

    protected function getFileSystem(): Filesystem
    {
        if (!isset($this->fileSystem)) {
            $this->fileSystem = new Filesystem();
        }
        return $this->fileSystem;
    }

    public function createCrud(MetaEntityInterface $metaEntity): array
    {
        $files[] = $this->createController($metaEntity);
        $files[] = $this->createForm($metaEntity);
        $files[] = $this->createViewTemplate($metaEntity, 'index');
        $files[] = $this->createViewTemplate($metaEntity, 'show');
        $files[] = $this->createViewTemplate($metaEntity, 'new');
        $files[] = $this->createViewTemplate($metaEntity, 'edit');
        $files[] = $this->createViewTemplate($metaEntity, 'delete');
        return $files;
    }

    protected function createController(MetaEntityInterface $metaEntity): string
    {
        return $this->createFile($metaEntity,'Controller', 'Controller');
    }

    protected function createForm(MetaEntityInterface $metaEntity): string
    {
        return $this->createFile($metaEntity,'Form', 'Type');
    }

    protected function createFile(MetaEntityInterface $metaEntity, $dirName, $fileSuffixName): string
    {
        $fileContent = $this->twig->render('@CrudGenerator/skeleton/'.strtolower($dirName).'/'.strtolower($fileSuffixName).'.php.twig', [
            'meta_entity' => $metaEntity,
        ]);
        $targetFile = str_replace(['/Entity', '.php'], ['/'.$dirName, $fileSuffixName.'.php'], $this->getTargetFile($metaEntity));

        $this->getFileSystem()->dumpFile($targetFile, $fileContent);

        return $targetFile;
    }

    protected function createViewTemplate(MetaEntityInterface $metaEntity, $action): string
    {
        $fileContent = $this->twig->render('@CrudGenerator/skeleton/templates/'.$action.'.html.twig.twig', [
            'meta_entity' => $metaEntity,
        ]);

        $targetFile = $this->projectDir .'/templates/'.
            ($metaEntity->getSubDir() ? Inflector::tableize($metaEntity->getSubDir()).'/' : '').
            Inflector::tableize($metaEntity->getName()). '/'.
            $action.'.html.twig';
        ;
        $this->getFileSystem()->dumpFile($targetFile, $fileContent);

        return $targetFile;
    }
}