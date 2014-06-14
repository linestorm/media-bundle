<?php

namespace LineStorm\MediaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command will re-index all databases
 *
 * Class IndexCommand
 *
 * @package LineStorm\SearchBundle\Command
 */
class ResizeMediaCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('linestorm:media:resize')
            ->setDescription('Resize all missing media versions')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $mediaManager = $container->get('linestorm.cms.media_manager');

        $mediaEntities = $mediaManager->findBy(array());

        foreach($mediaEntities as $media)
        {
            $output->writeln("Resizing {$media->getTitle()}");

            $mediaManager->resize($media);
        }

        $output->writeln("Finished");

    }
}
