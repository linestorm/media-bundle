<?php

namespace LineStorm\MediaBundle\Command;

use LineStorm\MediaBundle\Media\LocalStorageMediaProvider;
use LineStorm\MediaBundle\Model\Media;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command will clean up all unused images
 *
 * Class CleanupMediaCommand
 *
 * @package LineStorm\SearchBundle\Command
 */
class CleanupMediaCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('linestorm:media:purge-orphans')
            ->addOption('force', null, null, 'Force the purge')
            ->setDescription('Purges all orphaned images')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $mediaManager = $container->get('linestorm.cms.media_manager');

        $provider = $mediaManager->getDefaultProviderInstance();

        if(!$provider instanceof LocalStorageMediaProvider)
        {
            $output->writeln('You can only purge media from a local storage provider');
            return false;
        }

        /** @var Media[] $mediaEntities */
        $mediaEntities = $mediaManager->findBy(array());

        $output->writeln("Building Path Index");
        $paths = array();
        foreach($mediaEntities as $media)
        {
            $paths[] = $media->getPath();

            foreach($media->getVersions() as $version)
            {
                $paths[] = $version->getPath();
            }
        }

        $output->writeln("Calculating Purgable Images");

        $basePath = $mediaManager->getDefaultProviderInstance()->getStorePath();
        $files = glob($basePath.'*.{png,gif,jpg,jpeg}', GLOB_BRACE);

        $purgable = array_diff($files, $paths);
        $purgeCount = count($purgable);
        $output->writeln("Found {$purgeCount} images that can be purged");
        if(!$input->getOption('force'))
        {
            $output->writeln('You must use --force to execute the purge');
        }
        elseif($purgeCount > 0)
        {
            $output->writeln("Purging images Images");

            $progress = $this->getHelperSet()->get('progress');

            $progress->start($output, $purgeCount);
            foreach($purgable as $pfile)
            {
                unlink($pfile);
                $progress->advance();
            }

            $progress->finish();
        }
        else
        {
            $output->writeln("No images were purged, as none were found to be invalid");
        }

    }
}
