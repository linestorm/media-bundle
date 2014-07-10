<?php

namespace LineStorm\MediaBundle\Command;

use LineStorm\MediaBundle\Model\Media;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command will optimise all media
 *
 * Class IndexCommand
 *
 * @package LineStorm\SearchBundle\Command
 */
class OptimiseMediaCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('linestorm:media:optimise')
            ->setDescription('Optimise all media')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $mediaManager = $container->get('linestorm.cms.media_manager');
        $optimiser = $container->get('linestorm.cms.media.optimiser');

        /** @var Media[] $mediaEntities */
        $mediaEntities = $mediaManager->findBy(array());
        $entityCount = count($mediaEntities);

        if($entityCount > 0)
        {
            $output->writeln("Optimising {$entityCount} images");

            $progress = $this->getHelperSet()->get('progress');

            $progress->start($output, $entityCount);
            foreach($mediaEntities as $media)
            {
                $optimiser->optimise($media);
                $progress->advance();
            }

            $progress->finish();
        }
        else
        {
            $output->writeln("There were no images found to optimise");
        }

    }
}
