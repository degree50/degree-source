<?php

namespace App\DependencyInjection\Compiler;

use App\FileSystem\FileSystemService;
use Oneup\FlysystemBundle\DependencyInjection\Compiler\FilesystemPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Inializes Filesystems for @FileSystemService
 *
 * Modelled after {@see FilesystemPass}
 */
class FileSystemCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(FileSystemService::class)) {
            return;
        }

        $fileSystemServiceDefinition = $container->findDefinition(FileSystemService::class);

        $filesystems = $container->findTaggedServiceIds('oneup_flysystem.filesystem');

        $oneupFlysystemConfig = $container->getExtensionConfig('oneup_flysystem')[0];
        $adaptersConfig = $oneupFlysystemConfig['adapters'];
        $filesystemsConfig = $oneupFlysystemConfig['filesystems'];

        foreach ($filesystems as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['mount'])) {
                    continue;
                }

                $fileSystemConfig = $filesystemsConfig[$attribute['mount']];
                $adapterLocation = str_replace(
                    '%kernel.project_dir%',
                    $container->getParameter('kernel.project_dir'),
                    $adaptersConfig[$fileSystemConfig['adapter']]['local']['location'] . '/'
                );

                $fileSystemServiceDefinition->addMethodCall('registerFilesystem', [
                    new Reference($id),
                    $attribute['mount'],
                    $adapterLocation,
                ]);
            }
        }
    }
}
